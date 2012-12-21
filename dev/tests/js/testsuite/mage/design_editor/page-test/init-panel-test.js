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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
PageTestInitPanel = TestCase('DesignEditor_PageTest_InitPanel');
PageTestInitPanel.prototype.testInitPanel = function() {
    /*:DOC +=
        <div id="vde_toolbar_row"></div>
        <iframe name="vde_container_frame" id="vde_container_frame" class="vde_container_frame"></iframe>
    */
    var page = jQuery(window).vde_page();
    var frameSelector = page.vde_page('option', 'frameSelector');
    jQuery(frameSelector).triggerHandler('load');
    var panelSelector = page.vde_page('option', 'panelSelector');
    assertEquals(true, jQuery(panelSelector).is(':vde-vde_panel'));
    page.vde_page('destroy');
};
