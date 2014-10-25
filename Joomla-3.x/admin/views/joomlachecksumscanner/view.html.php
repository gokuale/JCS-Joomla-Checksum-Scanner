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

class JoomlaChecksumScannerViewJoomlaChecksumScanner extends JViewLegacy
{
    protected $_state;

    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('COM_JOOMLACHECKSUMSCANNER'), 'joomlachecksumscanner');
        JToolBarHelper::custom('getScanResult', 'eye', 'eye', JText::_('COM_JOOMLACHECKSUMSCANNER_DISPLAY_SCANRESULT'), true);

        if(JFactory::getUser()->authorise('core.delete', 'com_joomlachecksumscanner'))
        {
            JToolBarHelper::deleteList();
        }

        if(JFactory::getUser()->authorise('core.admin', 'com_joomlachecksumscanner'))
        {
            JToolBarHelper::preferences('com_joomlachecksumscanner', '500');
        }

        $this->snapshots = $this->get('Snapshots');
        $this->items = $this->get('Data');
        $this->pagination = $this->get('Pagination');
        $this->_state = $this->get('State');

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_joomlachecksumscanner/css/joomlachecksumscanner.css');

        // Get donation code message
        require_once JPATH_COMPONENT.'/helpers/joomlachecksumscanner.php';
        $donation_code_message = JoomlaChecksumScannerHelper::getDonationCodeMessage();
        $this->donation_code_message = $donation_code_message;

        JoomlaChecksumScannerHelper::addSubmenu('joomlachecksumscanner');
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }
}
