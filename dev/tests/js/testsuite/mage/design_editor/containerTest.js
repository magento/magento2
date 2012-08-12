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
ContainerTest = TestCase('ContainerTest');
ContainerTest.prototype.testInit = function() {
    /*:DOC += <div class="vde_element_wrapper vde_container" id="container"></div> */
    var container = jQuery('#container').vde_container();
    assertEquals(true, container.is(':vde-vde_container'));
    container.vde_container('destroy');
};
ContainerTest.prototype.testDefaultOptions = function() {
    /*:DOC += <div class="vde_element_wrapper vde_container" id="container"></div> */
    var container = jQuery('#container').vde_container();
    assertEquals('pointer', container.vde_container('option', 'tolerance'));
    assertEquals(true, container.vde_container('option', 'revert'));
    assertEquals('.vde_element_wrapper.vde_container', container.vde_container('option', 'connectWithSelector'));
    assertEquals('vde_placeholder', container.vde_container('option', 'placeholder'));
    assertEquals('vde_container_hover', container.vde_container('option', 'hoverClass'));
    assertEquals('.vde_element_wrapper.vde_draggable', container.vde_container('option', 'items'));
    assertEquals('clone', container.vde_container('option', 'helper'));
    assertEquals('body', container.vde_container('option', 'appendTo'));
    container.vde_container('destroy');
};
ContainerTest.prototype.testStartCallback = function() {
    /*:DOC +=
    <div>
         <div class="vde_element_wrapper vde_container" id="container" />
         <div class="vde_element_wrapper vde_container" />
    </div>
    */
    var containers = jQuery(".vde_element_wrapper.vde_container").vde_container();
    var container = jQuery("#container");
    var uiMock = {
        placeholder: jQuery('<div style="height:0px;"></div>'),
        helper: jQuery('<div style="height:100px;"></div>'),
        item: container
    }
    var startCallback = container.vde_container('option', 'start');
    startCallback('start', uiMock);
    assertEquals(false, 0 == uiMock.placeholder.outerHeight());
    var connectedWithOtherContainers = container.vde_container('option', 'connectWith').size() > 0;
    assertEquals(true, connectedWithOtherContainers);
    containers.vde_container('destroy');
}
ContainerTest.prototype.testOverCallback = function() {
    /*:DOC += <div class="vde_element_wrapper vde_container" id="container" /> */
    var container = jQuery("#container").vde_container();
    var hoverClass = container.vde_container('option', 'hoverClass');
    var overCallback = container.vde_container('option', 'over');
    overCallback('over', {});
    assertEquals(true, container.hasClass(hoverClass));
    container.vde_container('destroy');
}
ContainerTest.prototype.testOutCallback = function() {
    /*:DOC += <div class="vde_element_wrapper vde_container" id="container" /> */
    var container = jQuery("#container").vde_container();
    var hoverClass = container.vde_container('option', 'hoverClass');
    var outCallback = container.vde_container('option', 'out');
    outCallback('out', {});
    assertEquals(false, container.hasClass(hoverClass));
    container.vde_container('destroy');
}