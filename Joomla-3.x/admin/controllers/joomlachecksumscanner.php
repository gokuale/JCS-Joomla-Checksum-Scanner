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

class JoomlaChecksumScannerControllerJoomlachecksumscanner extends JControllerLegacy
{
    protected $_input;

    function __construct()
    {
        parent::__construct();

        $this->_input = JFactory::getApplication()->input;
    }

    /**
     * Starts the snapshot scan process
     *
     * @throws Exception
     */
    public function snapshotscan()
    {
        JSession::checkToken() OR jexit('Invalid Token');

        if(!JFactory::getUser()->authorise('joomlachecksumscanner.snapshotscan', 'com_joomlachecksumscanner'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->scanProcess('snapshotscan', 'snapshotScan');

    }

    /**
     * Starts the archive scan process
     *
     * @throws Exception
     */
    public function archivescan()
    {
        JSession::checkToken() OR jexit('Invalid Token');

        if(!JFactory::getUser()->authorise('joomlachecksumscanner.archivescan', 'com_joomlachecksumscanner'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->scanProcess('archivescan_snapshot', 'archiveScan');
    }

    /**
     * Provides a generic scan process for all types
     *
     * @param string $snapshot_name
     * @param string $scan_function
     *
     * @throws Exception
     */
    private function scanProcess($snapshot_name = 'snapshotscan', $scan_function = 'snapshotScan')
    {
        // Try to increase all relevant settings to prevent timeouts on big sites
        ini_set('memory_limit', '128M');
        ini_set('error_reporting', 0);
        @set_time_limit(3600);

        $snapshot_id = JFactory::getApplication()->input->get($snapshot_name, false, 'INTEGER');

        if(!empty($snapshot_id))
        {
            $model_snapshots = $this->getModel('snapshots');
            $snapshot_data = $model_snapshots->getSingleSnapshot($snapshot_id);
        }

        $model = $this->getModel('joomlachecksumscanner');

        if(!empty($snapshot_data) AND $scan_id = $model->$scan_function($snapshot_data))
        {
            $this->_input->set('view', 'scanresult');
            $this->_input->set('hidemainmenu', 1);
            $this->_input->set('id', $scan_id);
            parent::display();
        }
        else
        {
            $msg = JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_ERROR');
            $type = 'error';
            $this->setRedirect('index.php?option=com_joomlachecksumscanner', $msg, $type);
        }
    }

    /**
     * Loads the data for the scan result page
     */
    public function getScanResult()
    {
        JSession::checkToken() OR jexit('Invalid Token');

        if(!JFactory::getUser()->authorise('joomlachecksumscanner.scanresult', 'com_joomlachecksumscanner'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->_input->set('view', 'scanresult');
        $this->_input->set('hidemainmenu', 1);
        parent::display();
    }

    /**
     * Deletes selected entries
     */
    public function remove()
    {
        JSession::checkToken() OR jexit('Invalid Token');

        if(!JFactory::getUser()->authorise('core.delete', 'com_joomlachecksumscanner'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $model = $this->getModel('joomlachecksumscanner');

        if(!$model->delete())
        {
            $msg = JText::_('COM_JOOMLACHECKSUMSCANNER_DELETE_ERROR');
            $type = 'error';
        }
        else
        {
            $msg = JText::_('COM_JOOMLACHECKSUMSCANNER_DELETE_SUCCESS');
            $type = 'message';
        }

        $this->setRedirect(JRoute::_('index.php?option=com_joomlachecksumscanner', false), $msg, $type);
    }
}
