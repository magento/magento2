/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage.design_editor
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
MenuTest = TestCase('MenuTest');
MenuTest.prototype.testInit = function() {
    /*:DOC += <div id="menu"></div> */
    var menu = jQuery('#menu').vde_menu();
    assertEquals(true, menu.is(':vde-vde_menu'));
    menu.vde_menu('destroy');
};
MenuTest.prototype.testDefaultOptions = function() {
    /*:DOC += <div id="menu"></div> */
    var menu = jQuery('#menu').vde_menu();
    assertEquals('popup', menu.vde_menu('option', 'type'));
    assertEquals('.vde_toolbar_cell_title', menu.vde_menu('option', 'titleSelector'));
    assertEquals('.vde_toolbar_cell_value', menu.vde_menu('option', 'titleTextSelector'));
    assertEquals('active', menu.vde_menu('option', 'activeClass'));
    menu.vde_menu('destroy');
};
MenuTest.prototype.testMenuTreeSlimScrollInit = function() {
    /*:DOC +=
    <div id="menu">
        <div class="vde_toolbar_cell_title">
            Title:<span class="vde_toolbar_cell_value">Title Value</span>
        </div>
        <div class="vde_toolbar_cell_content">
            <div id="tree">
                <ul>
                    <li rel="tree_element"><a href="#link">Tree Element</a></li>
                </ul>
            </div>
        </div>
    </div>
     */
    jQuery.fx.off = true;
    var menu = jQuery('#menu').vde_menu({treeSelector:'#tree', slimScroll:true});
    var titleSelector = menu.vde_menu('option', 'titleSelector');
    jQuery(titleSelector).trigger('click.vde_menu');
    assertEquals(true, jQuery('#tree').is(':vde-vde_tree'));
    assertEquals(true, menu.find('.slimScrollBar').size() > 0);
    menu.vde_menu('destroy');
    jQuery.fx.off = false;
}
MenuTest.prototype.testChangeTitleEvent = function() {
    /*:DOC +=
    <div id="menu">
        <div class="vde_toolbar_cell_title">
            Title:<span class="vde_toolbar_cell_value">Title Value</span>
        </div>
        <div class="vde_toolbar_cell_content" />
    </div>
    */
    jQuery.fx.off = true;
    var menu = jQuery('#menu').vde_menu();
    var titleTextSelector = menu.vde_menu('option', 'titleSelector');
    var titleText = jQuery(titleTextSelector).text();
    menu.trigger('change_title.vde_menu', 'New Title');
    assertEquals(false, jQuery(titleTextSelector).text() == titleText);
    menu.vde_menu('destroy');
    jQuery.fx.off = false;
}
MenuTest.prototype.testLinkSelectedEvent = function() {
    /*:DOC +=
    <div id="menu">
        <div class="vde_toolbar_cell_title">
            Title:<span class="vde_toolbar_cell_value">Title Value</span>
        </div>
        <div class="vde_toolbar_cell_content" />
    </div>
    */
    jQuery.fx.off = true;
    var menu = jQuery('#menu').vde_menu();
    var titleTextSelector = menu.vde_menu('option', 'titleSelector');
    var titleText = jQuery(titleTextSelector).text();
    var a = jQuery('<a href="#">New Title</a>');
    menu.trigger('link_selected.vde_tree', [a]);
    assertEquals(false, jQuery(titleTextSelector).text() == titleText);
    menu.vde_menu('destroy');
    jQuery.fx.off = false;
}
MenuTest.prototype.testShowHide = function() {
    /*:DOC +=
    <div id="menu">
        <div class="vde_toolbar_cell_title">
            Title:<span class="vde_toolbar_cell_value">Title Value</span>
        </div>
        <div class="vde_toolbar_cell_content" />
    </div>
    */
    jQuery.fx.off = true;
    var menu = jQuery('#menu').vde_menu();
    var activeClass = menu.vde_menu('option', 'activeClass');
    var eventActiveToolbarCellIsTriggered = false;
    menu.on('activate_toolbar_cell.vde_menu', function() {eventActiveToolbarCellIsTriggered = true});
    menu.vde_menu('show');
    assertEquals(true, menu.hasClass(activeClass));
    assertEquals(true, eventActiveToolbarCellIsTriggered);
    menu.vde_menu('hide');
    assertEquals(false, menu.hasClass(activeClass));
    menu.vde_menu('destroy');
    jQuery.fx.off = false;
}
MenuTest.prototype.testHideMenuOnBodyClick = function() {
    /*:DOC +=
    <div id="menu">
        <div class="vde_toolbar_cell_title">
            Title:<span class="vde_toolbar_cell_value">Title Value</span>
        </div>
        <div class="vde_toolbar_cell_content" />
    </div>
    */
    jQuery.fx.off = true;
    var menu = jQuery('#menu').vde_menu();
    var activeClass = menu.vde_menu('option', 'activeClass');
    menu.vde_menu('show');
    jQuery('body').trigger('click');
    assertEquals(false, menu.hasClass(activeClass));
    menu.vde_menu('destroy');
    jQuery.fx.off = false;
}