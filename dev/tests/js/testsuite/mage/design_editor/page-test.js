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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
PageTest = TestCase('DesignEditor_PageTest');
PageTest.prototype.testInit = function() {
    var page = jQuery(window).vde_page();
    assertEquals(true, page.is(':vde-vde_page'));
    page.vde_page('destroy');
};
PageTest.prototype.testDefaultOptions = function() {
    var page = jQuery(window).vde_page();
    assertEquals('iframe#vde_container_frame', page.vde_page('option', 'frameSelector'));
    assertEquals('.vde_element_wrapper.vde_container', page.vde_page('option', 'containerSelector'));
    assertEquals('#vde_toolbar_row', page.vde_page('option', 'panelSelector'));
    assertEquals('.vde_element_wrapper', page.vde_page('option', 'highlightElementSelector'));
    assertEquals('.vde_element_title', page.vde_page('option', 'highlightElementTitleSelector'));
    assertEquals('#vde_highlighting', page.vde_page('option', 'highlightCheckboxSelector'));
    page.vde_page('destroy');
};
PageTest.prototype.testInitHighlighting = function() {
    /*:DOC += <div id="vde_toolbar_row"><div id="vde_highlighting"></div></div> */
    var page = jQuery(window).vde_page();
    var highlightCheckboxSelector = page.vde_page('option', 'highlightCheckboxSelector');
    assertEquals(true, jQuery(highlightCheckboxSelector).is(':vde-vde_checkbox'));
    page.vde_page('destroy');
};
PageTest.prototype.testDestroy = function() {
    /*:DOC +=
     <div id="vde_toolbar_row"></div>
     <div class="vde_history_toolbar"></div>
     <div class="vde_element_wrapper vde_container"></div>
     */

    jQuery(window).vde_page();
    jQuery(window).vde_page('destroy');

    //check no garbage is left
    assertFalse($('#vde_toolbar_row').is(':vde-vde_panel'));
    assertFalse($('.vde_history_toolbar').is(':vde-vde_historyToolbar'));
    assertFalse($(window).is(':vde-vde_history'));
    assertFalse($('.vde_element_wrapper').is(':vde-vde_removable'));
    assertFalse($('.vde_element_wrapper.vde_container').is(':vde-vde_container'));
};

