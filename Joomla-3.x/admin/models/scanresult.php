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

class JoomlaChecksumScannerModelScanresult extends JModelLegacy
{
    protected $_scan_result;

    function __construct()
    {
        parent::__construct();

        $this->_db = JFactory::getDbo();
        $this->_input = JFactory::getApplication()->input;
    }

    /**
     * Loads the (selected) scan result from the database
     *
     * @return array
     */
    function getScanresult()
    {
        $scan_id = 0;
        $ids = $this->_input->get('id', 0, 'ARRAY');

        if(!empty($ids[0]))
        {
            $scan_id = $ids[0];
        }

        if(empty($this->_scan_result) AND !empty($scan_id))
        {
            $query = $this->_db->getQuery(true);

            $query->select('*');
            $query->from('#__joomlachecksumscanner');
            $query->where('id ='.(int)$scan_id);

            $result = $this->_getList($query);

            if(!empty($result[0]))
            {
                $this->_scan_result = (array)$result[0];
                $this->_scan_result['data'] = json_decode($this->_scan_result['data'], true);
            }
        }

        return $this->_scan_result;
    }
}
