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
    <script type="text/javascript">
        // Load the loading animation after a click on the action button
        jQuery(document).ready(function () {
            document.id('button_snapshotscan').addEvent('click', function () {
                jQuery("#controls_snapshotscan").fadeOut("slow", function () {
                    jQuery("#loading_snapshotscan").fadeIn("slow", "linear");
                    jQuery("#controls_archivescan").fadeOut("slow", "linear");
                });
            });
            document.id('button_archivescan').addEvent('click', function () {
                jQuery("#controls_archivescan").fadeOut("slow", function () {
                    jQuery("#loading_archivescan").fadeIn("slow", "linear");
                    jQuery("#controls_snapshotscan").fadeOut("slow", "linear");
                });
            });
        });
    </script>
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_joomlachecksumscanner'); ?>"
          method="post" name="adminForm"
          id="adminForm">
        <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif; ?>
                <div id="checksum-scanner-type-selection">
                    <div id="checksum-scanner-type-selection-snapshots">
                        <h2><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_SNAPSHOTS'); ?></h2>
                        <?php if(!empty($this->snapshots)) : ?>
                            <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_SNAPSHOT_SELECT_DESC'); ?></p>
                            <div class="control-group">
                                <div class="control-label">
                                    <label title="" class="hasTooltip" for="jform_checksum_algorithm"
                                           id="jform_checksum_algorithm-lbl" data-original-title="">
                                        <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_SNAPSHOT_SELECT'); ?>
                                    </label>
                                </div>
                                <div class="controls">
                                    <div id="controls_snapshotscan">
                                        <select name="snapshotscan" id="snapshotscan">
                                            <?php foreach($this->snapshots as $snapshot) : ?>
                                                <option
                                                    value="<?php echo $snapshot->id; ?>"><?php echo JHTML::_('date', $snapshot->date, JText::_('DATE_FORMAT_LC2')).' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_FILES').': '.$snapshot->count; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary" id="button_snapshotscan"
                                                onclick="Joomla.submitbutton('snapshotscan')"><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_SNAPSHOT_START_PROCESS'); ?></button>
                                    </div>
                                    <div id="loading_snapshotscan" style="display: none;">
                                        <img src="components/com_joomlachecksumscanner/images/loading.gif"
                                             alt="Loading..."/> <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_CHECKPROCESSWAIT'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_SNAPSHOT_NO_ITEMS'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div id="checksum-scanner-type-selection-archive">
                        <h2><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_ARCHIVE'); ?></h2>
                        <p><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_ARCHIVE_SELECT_DESC'); ?></p>
                        <div class="control-group">
                            <div class="control-label">
                                <label title="" class="hasTooltip" for="jform_checksum_algorithm"
                                       id="jform_checksum_algorithm-lbl" data-original-title="">
                                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_ARCHIVE_SELECT'); ?>
                                </label>
                            </div>
                            <div class="controls">
                                <div id="controls_archivescan">
                                    <select name="archivescan_snapshot" id="archivescan_snapshot">
                                        <?php foreach($this->snapshots as $snapshot) : ?>
                                            <option
                                                value="<?php echo $snapshot->id; ?>"><?php echo JHTML::_('date', $snapshot->date, JText::_('DATE_FORMAT_LC2')).' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_FILES').': '.$snapshot->count; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="file" name="archivescan_package" id="archivescan_package"
                                           class="input_box"/>
                                    <button class="btn btn-primary" id="button_archivescan"
                                            onclick="Joomla.submitbutton('archivescan')"><?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_MAIN_ARCHIVE_START_PROCESS'); ?></button>
                                </div>
                                <div id="loading_archivescan" style="display: none;">
                                    <img src="components/com_joomlachecksumscanner/images/loading.gif"
                                         alt="Loading..."/> <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_CHECKPROCESSWAIT'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="filter-bar" class="btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <label for="filter_search"
                               class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
                        <input type="text" name="filter_search" id="filter_search"
                               placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                               value="<?php echo $this->escape($this->_state->get('filter.search')); ?>"
                               class="hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER'); ?>"/>
                    </div>
                    <div class="btn-group pull-left hidden-phone">
                        <button type="submit" class="btn hasTooltip"
                                title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i>
                        </button>
                        <button type="button" class="btn hasTooltip"
                                title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value = '';
                        this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <div class="btn-group pull-right hidden-phone">
                        <label for="limit"
                               class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
                        <?php echo $this->pagination->getLimitBox(); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
                <table id="articleList" class="table table-striped">
                    <thead>
                    <tr>
                        <th width="20">
                            <input type="checkbox" name="checkall-toggle" value=""
                                   title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                                   onclick="Joomla.checkAll(this)"/>
                        </th>
                        <th width="5%">
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_ID'); ?>
                        </th>
                        <th width="15%">
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_DATE'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_SCANPREVIEW'); ?>
                        </th>
                        <th width="10%">
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_SCANTYPE'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $k = 0;
                    $n = count($this->items);

                    for($i = 0; $i < $n; $i++)
                    {
                        $row = $this->items[$i];
                        $checked = JHTML::_('grid.id', $i, $row->id, false, 'id');
                        ?>
                        <tr class="<?php echo 'row'.$k; ?>">
                            <td>
                                <?php echo $checked; ?>
                            </td>
                            <td class="small">
                            <span class="hasTooltip" title="<?php echo htmlspecialchars($row->id); ?>">
                                <?php echo $row->id; ?>
                            </span>
                            </td>
                            <td class="small">
                            <span class="hasTooltip" title="<?php echo $row->date; ?>">
                                <?php echo JHTML::_('date', $row->date, JText::_('DATE_FORMAT_LC2')); ?>
                            </span>
                            </td>
                            <td class="small">
                                <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_TYPES_NEW').': '.$row->data_preview->new.' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_TYPES_MODIFIED').': '.$row->data_preview->modified.' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_TYPES_REMOVED').': '.$row->data_preview->removed.' - '.JText::_('COM_JOOMLACHECKSUMSCANNER_SCAN_TYPES_IDENTICAL').': '.$row->data_preview->identical; ?>
                            </td>
                            <td class="small">
                            <span class="hasTooltip" title="<?php echo htmlspecialchars($row->scan_type); ?>">
                                <?php echo $row->scan_type; ?>
                            </span>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                    </tbody>
                </table>
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