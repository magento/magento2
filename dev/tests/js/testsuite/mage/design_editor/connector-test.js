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
ConnectorTest = TestCase('DesignEditor_ConnectorTest');
ConnectorTest.prototype.testDefaultOptions = function() {
    var connector = jQuery(window).vde_connector();
    assertEquals('.vde_element_wrapper.vde_container', connector.vde_connector('option', 'containerSelector'));
    assertEquals('.vde_element_wrapper', connector.vde_connector('option', 'highlightElementSelector'));
    assertEquals('.vde_element_title', connector.vde_connector('option', 'highlightElementTitleSelector'));
    assertEquals('#vde_highlighting', connector.vde_connector('option', 'highlightCheckboxSelector'));
    assertEquals('.vde_history_toolbar', connector.vde_connector('option', 'historyToolbarSelector'));
    connector.vde_connector('destroy');
};
ConnectorTest.prototype.testInitHistory = function() {
    var connector = jQuery(window).vde_connector();
    assertEquals(true, jQuery(window).is(':vde-vde_history'));
    connector.vde_connector('destroy');
};
ConnectorTest.prototype.testInitHistoryToolbar = function() {
    /*:DOC += <div class="vde_history_toolbar"></div> */
    var connector = jQuery(window).vde_connector();
    var container = jQuery('.vde_history_toolbar');
    assertEquals(true, container.is(':vde-vde_historyToolbar'));
    assertNotNull(container.data('vde_historyToolbar')._history);
    connector.vde_connector('destroy');
};
ConnectorTest.prototype.testInitRemoveOperation = function() {
    /*:DOC += <div class="vde_element_wrapper vde-vde_removable"></div> */
    var connector = jQuery(window).vde_connector();
    var containers = jQuery('.vde_element_wrapper');
    assertNotNull(containers.data('vde_removable').history);
    connector.vde_connector('destroy');
};
ConnectorTest.prototype.testSetHistoryForContainers = function() {
    var connector = jQuery(window).vde_connector();
    var containers = jQuery('.vde_element_wrapper.vde_container');
    assertNotNull(containers.vde_container('getHistory'));
    connector.vde_connector('destroy');
};
ConnectorTest.prototype.testDestroy = function() {
    /*:DOC +=
     <div class="vde_history_toolbar"></div>
     <div class="vde_element_wrapper vde_container"></div>
     */

    var connector = jQuery(window).vde_connector();
    connector.vde_connector('destroy');

    //check no garbage is left
    assertFalse($('#vde_toolbar').is(':vde-vde_panel'));
    assertFalse($('.vde_history_toolbar').is(':vde-vde_historyToolbar'));
    assertFalse($(window).is(':vde-vde_history'));
    assertFalse($('.vde_element_wrapper').is(':vde-vde_removable'));
    assertFalse($('.vde_element_wrapper.vde_container').is(':vde-vde_container'));
};
