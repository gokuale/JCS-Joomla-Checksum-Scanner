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
            document.id('toolbar-new').addEvent('click', function () {
                jQuery("#loading_countfiles").fadeIn("slow", "linear");
            });
        });
    </script>
    <form action="<?php echo JRoute::_('index.php?option=com_joomlachecksumscanner&view=filescount'); ?>" method="post"
          name="adminForm" id="adminForm">
        <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif; ?>
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
                <div id="loading_countfiles" style="display: none; text-align: center;">
                    <img src="components/com_joomlachecksumscanner/images/loading.gif"
                         alt="Loading..."/>
                    <br/><br/>
                    <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_CHECKPROCESSWAIT'); ?>
                </div>
                <table id="articleList" class="table table-striped">
                    <thead>
                    <tr>
                        <th width="20">
                            <input type="checkbox" name="checkall-toggle" value=""
                                   title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                                   onclick="Joomla.checkAll(this)"/>
                        </th>
                        <th width="15%">
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_DATE'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('COM_JOOMLACHECKSUMSCANNER_TABLE_NUMBEROFFILES'); ?>
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
                            <span class="hasTooltip" title="<?php echo $row->date; ?>">
                                <?php echo JHTML::_('date', $row->date, JText::_('DATE_FORMAT_LC2')); ?>
                            </span>
                            </td>
                            <td class="small">
                            <span class="hasTooltip" title="<?php echo htmlspecialchars($row->number); ?>">
                                <?php echo $row->number; ?>
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
                <input type="hidden" name="controller" value="filescount"/>
                <?php echo JHTML::_('form.token'); ?>
            </div>
    </form>
    <div style="text-align: center; margin-top: 10px;">
        <p><?php echo JText::sprintf('COM_JOOMLACHECKSUMSCANNER_VERSION', _JOOMLACHECKSUMSCANNER_VERSION) ?></p>
    </div>
<?php echo $this->donation_code_message; ?>