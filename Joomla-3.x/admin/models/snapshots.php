<?php
/**
 * JCS - Joomla Checksum Scanner for Joomal! 3.x
 * License: GNU/GPL - http://www.gnu.org/licenses/gpl.html
 * Author: Viktor Vogel
 * Project page: http://joomla-extensions.kubik-rubik.de/jcs-joomla-checksum-scanner
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class JoomlaChecksumScannerModelSnapshots extends JModelLegacy
{
    protected $_checksum_array = array();
    protected $_db;
    protected $_error;
    protected $_input;
    protected $_pagination;
    protected $_params;
    protected $_singlesnapshot;
    protected $_snapshot_datetime;
    protected $_total;

    function __construct()
    {
        parent::__construct();
        $app = JFactory::getApplication();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', JApplicationWeb::getInstance()->get('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest('joomlachecksumscanner.limitstart', 'limitstart', 0, 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $search = $app->getUserStateFromRequest('joomlachecksumscanner.filter.search', 'filter_search', null);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
        $this->setState('filter.search', $search);

        $this->_db = JFactory::getDbo();
        $this->_input = JFactory::getApplication()->input;
        $this->_params = JComponentHelper::getParams('com_joomlachecksumscanner');
        $this->_snapshot_datetime = JFactory::getDate('now', JFactory::getApplication()->getCfg('offset'));
    }

    /**
     * Loads all or filtered entries from the database
     *
     * @return array
     */
    function getData()
    {
        if(empty($this->_data))
        {
            $query = $this->_db->getQuery(true);

            $query->select('a.id, a.date, a.data_preview, a.count, a.algorithm');
            $query->from('#__joomlachecksumscanner_snapshots AS a');

            $search = $this->getState('filter.search');

            if(!empty($search))
            {
                $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
                $query->where('(a.date LIKE '.$search.') OR (a.data_preview LIKE '.$search.') OR (a.count LIKE '.$search.') OR (a.algorithm LIKE '.$search.')');
            }

            $query->order($this->_db->escape('date DESC'));

            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }

    /**
     * Loads a single entry from the database
     *
     * @return array
     */
    function getSingleSnapshot($id)
    {
        if(empty($this->_singlesnapshot))
        {
            $this->_singlesnapshot = array();

            $query = $this->_db->getQuery(true);

            $query->select('*');
            $query->from('#__joomlachecksumscanner_snapshots');
            $query->where('id ='.(int)$id);

            $result = $this->_getList($query);

            if(!empty($result[0]))
            {
                $this->_singlesnapshot = $result[0];
            }
        }

        return $this->_singlesnapshot;
    }

    /**
     * Creates the pagination in the footer of the list
     *
     * @return JPagination
     */
    function getPagination()
    {
        if(empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    /**
     * Calculates the total number of all loaded entries
     *
     * @return int
     */
    function getTotal()
    {
        if(empty($this->_total))
        {
            $query = $this->_db->getQuery(true);

            $query->select('*');
            $query->from('#__joomlachecksumscanner_snapshots AS a');

            $search = $this->getState('filter.search');

            if(!empty($search))
            {
                $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
                $query->where('(a.date LIKE '.$search.') OR (a.data LIKE '.$search.')');
            }

            $query->order($this->_db->escape('date ASC'));

            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    /**
     * Main function for the snapshot scan process
     *
     * @return bool
     */
    function createSnapshot()
    {
        // Prepare files which should be excluded
        $exclude_files = $this->_params->get('exclude_files');

        if(!empty($exclude_files))
        {
            $exclude_files = array_map('trim', explode("\n", $exclude_files));
        }
        else
        {
            $exclude_files = array();
        }

        // Prepare folders which should be excluded
        $exclude_folders = $this->_params->get('exclude_folders');

        if(!empty($exclude_folders))
        {
            $exclude_folders = array_map('trim', explode("\n", $exclude_folders));
        }
        else
        {
            $exclude_folders = array();
        }

        $checksum_algorithm = $this->_params->get('checksum_algorithm', 'md5_file');

        $this->getChecksumData(JPATH_ROOT, $exclude_files, $exclude_folders, $checksum_algorithm);

        if(!empty($this->_checksum_array) AND is_array($this->_checksum_array))
        {
            // Add path of table - this is important for the system plugin
            JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_joomlachecksumscanner/tables');
            $table = $this->getTable('snapshots', 'JoomlaChecksumScannerTable');

            $checksum_data = json_encode($this->_checksum_array);

            $data = array();
            $data['date'] = $this->_snapshot_datetime->toSql();
            $data['data'] = $checksum_data;
            $data['data_preview'] = mb_substr($checksum_data, 0, 250).'...';
            $data['count'] = count($this->_checksum_array);
            $data['algorithm'] = $checksum_algorithm;

            if(!$table->save($data))
            {
                $this->setError($this->_db->getErrorMsg());
                $this->_error = 'database';

                return false;
            }

            return true;
        }
    }

    /**
     * Loads all files and (sub-)folders recursively to create the checksum list
     *
     * @param string $folder
     * @param array  $exclude_files
     * @param array  $exclude_folders
     * @param string $checksum_algorithm
     * @param string $folder_relative
     *
     * @return bool
     */
    private function getChecksumData($folder, $exclude_files = array(), $exclude_folders = array(), $checksum_algorithm = 'md5_file', $folder_relative = '')
    {
        // Open the called folder path
        if(!$dir = @opendir($folder))
        {
            return false;
        }

        while($file = readdir($dir))
        {
            if(is_dir($folder.'/'.$file) AND $file != '.' AND $file != '..')
            {
                if(!empty($exclude_folders))
                {
                    if(in_array($folder_relative.$file, $exclude_folders))
                    {
                        continue;
                    }
                }

                $this->getChecksumData($folder.'/'.$file, $exclude_files, $exclude_folders, $checksum_algorithm, $folder_relative.$file.'/');
            }
            elseif(is_file($folder.'/'.$file))
            {
                if(!empty($exclude_files))
                {
                    if(in_array($folder_relative.$file, $exclude_files))
                    {
                        continue;
                    }
                }

                $this->_checksum_array[$folder_relative.$file] = $checksum_algorithm($folder.'/'.$file);
            }
        }

        closedir($dir);

        return true;
    }

    /**
     * Deletes snapshot entries from the database
     *
     * @return boolean
     */
    function delete()
    {
        $ids = $this->_input->get('id', 0, 'ARRAY');
        $table = $this->getTable('snapshots', 'JoomlaChecksumScannerTable');

        foreach($ids as $id)
        {
            if(!$table->delete($id))
            {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }
        }

        return true;
    }
}
