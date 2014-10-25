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

class JoomlaChecksumScannerModelJoomlaChecksumScanner extends JModelLegacy
{
    protected $_checksum_array = array();
    protected $_db;
    protected $_error;
    protected $_input;
    protected $_pagination;
    protected $_params;
    protected $_result_array;
    protected $_scanresult_datetime;
    protected $_snapshots;
    protected $_snapshot_checksums;
    protected $_total;
    protected $_scan_type;

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
        $this->_scanresult_datetime = JFactory::getDate('now', JFactory::getApplication()->getCfg('offset'));
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

            $query->select('a.id, a.date, a.data_preview, a.scan_type');
            $query->from('#__joomlachecksumscanner AS a');

            $search = $this->getState('filter.search');

            if(!empty($search))
            {
                $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
                $query->where('(a.date LIKE '.$search.') OR (a.data_preview LIKE '.$search.') OR (a.scan_type LIKE '.$search.')');
            }

            $query->order($this->_db->escape('date DESC'));

            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

            if(!empty($this->_data) AND is_array($this->_data))
            {
                foreach($this->_data as &$entry)
                {
                    $entry->data_preview = json_decode($entry->data_preview);
                }
            }
        }

        return $this->_data;
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
            $query->from('#__joomlachecksumscanner AS a');

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
     * Loads all snapshots from the database
     *
     * @return array
     */
    function getSnapshots()
    {
        if(empty($this->_snapshots))
        {
            $query = $this->_db->getQuery(true);

            $query->select('a.id, a.date, a.data_preview, a.count');
            $query->from('#__joomlachecksumscanner_snapshots AS a');
            $query->order($this->_db->escape('date DESC'));

            $this->_snapshots = $this->_getList($query);
        }

        return $this->_snapshots;
    }

    /**
     * Starts the snapshot scan and saves result into the database
     *
     * @param $snapshot_data
     *
     * @return bool
     * @throws Exception
     */
    public function snapshotScan($snapshot_data)
    {
        $this->_scan_type = 'snapshot';
        $this->_snapshot_checksums = json_decode($snapshot_data->data, true);
        $this->_result_array = array('new' => array(), 'removed' => array(), 'modified' => array(), 'identical' => array());

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

        $checksum_algorithm = $snapshot_data->algorithm;

        $this->checkChecksums(JPATH_ROOT, $exclude_files, $exclude_folders, $checksum_algorithm);

        if(!empty($this->_snapshot_checksums))
        {
            $this->_result_array['removed'] = $this->getRemovedFiles($exclude_folders, $exclude_files);
        }

        if(!empty($this->_result_array) AND is_array($this->_result_array))
        {
            // Add path of table - this is important for the system plugin
            JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_joomlachecksumscanner/tables');
            $table = $this->getTable('joomlachecksumscanner', 'JoomlaChecksumScannerTable');

            $data = array();
            $data['date'] = $this->_scanresult_datetime->toSql();
            $data['data'] = json_encode($this->_result_array);
            $data['data_preview'] = json_encode(array('new' => count($this->_result_array['new']), 'modified' => count($this->_result_array['modified']), 'removed' => count($this->_result_array['removed']), 'identical' => count($this->_result_array['identical'])));
            $data['scan_type'] = 'snapshot';

            if(!$table->save($data))
            {
                $this->setError($this->_db->getErrorMsg());
                $this->_error = 'database';

                return false;
            }

            return $table->id;
        }
    }

    /**
     * Starts the snapshot scan and saves result into the database
     *
     * @param $snapshot_data
     *
     * @return bool
     * @throws Exception
     */
    public function archiveScan($snapshot_data)
    {
        $this->_scan_type = 'archive';
        $this->_snapshot_checksums = json_decode($snapshot_data->data, true);
        $this->_result_array = array('new' => array(), 'removed' => array(), 'modified' => array(), 'identical' => array());

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

        $checksum_algorithm = $snapshot_data->algorithm;

        // Upload the ZIP archive in the tmp folder an do the scan from this folder
        $package_uploaded = $this->getPackageFromUpload();

        if(!empty($package_uploaded))
        {
            $this->checkChecksums($package_uploaded['extract_dir'], $exclude_files, $exclude_folders, $checksum_algorithm);

            $this->remove_archive_data($package_uploaded['package_file'], $package_uploaded['extract_dir']);

            if(!empty($this->_snapshot_checksums))
            {
                $this->_result_array['new'] = $this->getRemovedFiles($exclude_folders, $exclude_files);
            }

            if(!empty($this->_result_array) AND is_array($this->_result_array))
            {
                // Add path of table - this is important for the system plugin
                JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_joomlachecksumscanner/tables');
                $table = $this->getTable('joomlachecksumscanner', 'JoomlaChecksumScannerTable');

                $data = array();
                $data['date'] = $this->_scanresult_datetime->toSql();
                $data['data'] = json_encode($this->_result_array);
                $data['data_preview'] = json_encode(array('new' => count($this->_result_array['new']), 'modified' => count($this->_result_array['modified']), 'removed' => count($this->_result_array['removed']), 'identical' => count($this->_result_array['identical'])));
                $data['scan_type'] = 'archive';

                if(!$table->save($data))
                {
                    $this->setError($this->_db->getErrorMsg());
                    $this->_error = 'database';

                    return false;
                }

                return $table->id;
            }
        }
    }

    /**
     * Loads all files and (sub-)folders recursively and compare with snapshot
     *
     * @param string $folder
     * @param array  $exclude_files
     * @param array  $exclude_folders
     * @param string $checksum_algorithm
     * @param string $folder_relative
     *
     * @return bool
     */
    private function checkChecksums($folder, $exclude_files = array(), $exclude_folders = array(), $checksum_algorithm = 'md5_file', $folder_relative = '')
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

                $this->checkChecksums($folder.'/'.$file, $exclude_files, $exclude_folders, $checksum_algorithm, $folder_relative.$file.'/');
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

                if(!empty($this->_snapshot_checksums[$folder_relative.$file]))
                {
                    if($this->_snapshot_checksums[$folder_relative.$file] == $checksum_algorithm($folder.'/'.$file))
                    {
                        $this->_result_array['identical'][] = $folder_relative.$file;
                    }
                    else
                    {
                        $this->_result_array['modified'][] = $folder_relative.$file;
                    }

                    unset($this->_snapshot_checksums[$folder_relative.$file]);
                }
                else
                {
                    if($this->_scan_type == 'snapshot')
                    {
                        $this->_result_array['new'][] = $folder_relative.$file;
                    }
                    elseif($this->_scan_type == 'archive')
                    {
                        $this->_result_array['removed'][] = $folder_relative.$file;
                    }
                }
            }
        }

        closedir($dir);

        return true;
    }

    /**
     * Adds only files to the removed files list if they are not excluded in the settings
     *
     * @param array $exclude_folders
     * @param array $exclude_files
     *
     * @return array
     */
    private function getRemovedFiles($exclude_folders, $exclude_files)
    {
        $removed_snapshots = array();

        foreach($this->_snapshot_checksums as $snapshot_path => $snapshot_checksum)
        {
            if(in_array($snapshot_path, $exclude_files))
            {
                continue;
            }

            foreach($exclude_folders as $exclude_folder)
            {
                if(strpos($snapshot_path.'/', $exclude_folder) === 0)
                {
                    continue 2;
                }
            }

            $removed_snapshots[] = $snapshot_path;
        }

        return $removed_snapshots;
    }

    /**
     * Get the files from the uploaded archive for the scan process
     *
     * Based on _getPackageFromUpload - class InstallerModelInstall
     *
     * @return package definition or false on failure
     */
    private function getPackageFromUpload()
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.path');

        $package = array();

        // Get the uploaded file information
        $user_file = $this->_input->files->get('archivescan_package', null, 'array');

        if(!(bool)ini_get('file_uploads') OR !extension_loaded('zlib') OR !is_array($user_file) OR $user_file['error'] OR $user_file['size'] < 1)
        {
            return false;
        }

        $tmp_dest = JFactory::getConfig()->get('tmp_path').'/'.$user_file['name'];

        jimport('joomla.filesystem.file');
        JFile::upload($user_file['tmp_name'], $tmp_dest);

        $upload_dir = uniqid('jcs_');
        $extract_dir = JPath::clean(dirname($tmp_dest) . '/' . $upload_dir);
        $archive_name = JPath::clean($tmp_dest);

        try
        {
            JArchive::extract($archive_name, $extract_dir);
        }
        catch (Exception $e)
        {
            return false;
        }

        $package['extract_dir'] = $extract_dir;
        $package['package_file'] = $archive_name;

        return $package;
    }

    /**
     * Clean up temporary uploaded package and unpacked files
     *
     * Based on JInstallerHelper::cleanupInstall
     *
     * @param   string  $package    Path to the uploaded package file
     * @param   string  $resultdir  Path to the unpacked files
     *
     * @return  boolean  True on success
     */
    private function remove_archive_data($package, $resultdir)
    {
        if(!empty($resultdir) AND is_dir($resultdir))
        {
            JFolder::delete($resultdir);
        }

        $config = JFactory::getConfig();

        if(is_file($package))
        {
            JFile::delete($package);
        }
        elseif(is_file(JPath::clean($config->get('tmp_path').'/'.$package)))
        {
            JFile::delete(JPath::clean($config->get('tmp_path').'/'.$package));
        }
    }

    /**
     * Deletes database entries
     *
     * @return boolean
     */
    function delete()
    {
        $ids = $this->_input->get('id', 0, 'ARRAY');
        $table = $this->getTable('joomlachecksumscanner', 'JoomlaChecksumScannerTable');

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
