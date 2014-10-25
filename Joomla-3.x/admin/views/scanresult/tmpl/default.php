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
JHtml::_('behavior.multiselect');
?>
    <form action="<?php echo JRoute::_('index.php?option=com_joomlachecksumscanner'); ?>" method="post" name="adminForm"
          id="adminForm">
        <div id="j-main-container">
            <?php if(!empty($this->scan_result)) : ?>
                <h2><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_TITLE'); ?></h2>
                <p>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_ID').': '.$this->scan_result['id'].' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_DATE').': '.JHTML::_('date', $this->scan_result['date'], JText::_('DATE_FORMAT_LC2')); ?>
                </p>
                <h3>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_NEW').' ('.count($this->scan_result['data']['new']).')'; ?>
                </h3>
                <?php if(!empty($this->scan_result['data']['new'])) : ?>
                    <ul>
                        <?php foreach($this->scan_result['data']['new'] as $file_new) : ?>
                            <li>
                                <?php echo $file_new; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_NOCHANGES'); ?></p>
                <?php endif; ?>
                <h3>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_MODIFIED').' ('.count($this->scan_result['data']['modified']).')'; ?>
                </h3>
                <?php if(!empty($this->scan_result['data']['modified'])) : ?>
                    <ul>
                        <?php foreach($this->scan_result['data']['modified'] as $file_modified) : ?>
                            <li>
                                <?php echo $file_modified; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_NOCHANGES'); ?></p>
                <?php endif; ?>
                <h3>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_REMOVED').' ('.count($this->scan_result['data']['removed']).')'; ?>
                </h3>
                <?php if(!empty($this->scan_result['data']['removed'])) : ?>
                    <ul>
                        <?php foreach($this->scan_result['data']['removed'] as $file_removed) : ?>
                            <li>
                                <?php echo $file_removed; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_NOCHANGES'); ?></p>
                <?php endif; ?>
                <h3>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_IDENTICAL').' ('.count($this->scan_result['data']['identical']).')'; ?>
                </h3>
                <?php if(!empty($this->scan_result['data']['identical'])) : ?>
                    <ul>
                        <?php foreach($this->scan_result['data']['identical'] as $file_identical) : ?>
                            <li>
                                <?php echo $file_identical; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCANRESULT_NOCHANGES'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <input type="hidden" name="option" value="com_joomlachecksumscanner"/>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="controller" value="joomlachecksumscanner"/>
            <?php echo JHTML::_('form.token'); ?>
        </div>
    </form>
    <div style="text-align: center; margin-top: 10px;">
        <p><?php echo JText::sprintf('COM_JOOMLACHECKSUMSCANNER_VERSION', _JOOMLACHECKSUMSCANNER_VERSION) ?></p>
    </div>
<?php echo $this->donation_code_message; ?>