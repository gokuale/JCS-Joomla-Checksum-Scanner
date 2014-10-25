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

class JoomlaChecksumScannerControllerSnapshots extends JControllerLegacy
{
    protected $_input;

    function __construct()
    {
        parent::__construct();

        $this->_input = JFactory::getApplication()->input;
    }

    /**
     * Creates a new snapshots
     */
    function snapshot()
    {
        JSession::checkToken() OR jexit('Invalid Token');

        if(!JFactory::getUser()->authorise('joomlachecksumscanner.snapshotcreate', 'com_joomlachecksumscanner'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // Try to increase all relevant settings to prevent timeouts on big sites
        ini_set('memory_limit', '128M');
        ini_set('error_reporting', 0);
        @set_time_limit(3600);

        $model = $this->getModel('snapshots');

        if($model->createSnapshot())
        {
            $msg = JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_ERROR');
            $type = 'error';
        }

        $this->setRedirect('index.php?option=com_joomlachecksumscanner&view=snapshots', $msg, $type);
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

        $model = $this->getModel('snapshots');

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

        $this->setRedirect(JRoute::_('index.php?option=com_joomlachecksumscanner&view=snapshots', false), $msg, $type);
    }
}
