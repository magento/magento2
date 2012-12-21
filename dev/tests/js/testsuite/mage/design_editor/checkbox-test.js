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
CheckboxTest = TestCase('DesignEditor_CheckboxTest');
CheckboxTest.prototype.testInit = function() {
    /*:DOC += <div id="checkbox"></div> */
    var checkbox = jQuery('#checkbox').vde_checkbox();
    assertEquals(true, checkbox.is(':vde-vde_checkbox'));
    checkbox.vde_checkbox('destroy');
};
CheckboxTest.prototype.testDefaultOptions = function() {
    /*:DOC += <div id="checkbox"></div> */
    var checkbox = jQuery('#checkbox').vde_checkbox();
    assertEquals('checked', checkbox.vde_checkbox('option', 'checkedClass'));
    checkbox.vde_checkbox('destroy');
};
CheckboxTest.prototype.testClickEvent = function() {
    /*:DOC += <div id="checkbox"></div> */
    var checkbox = jQuery('#checkbox').vde_checkbox();
    var checkedClass = checkbox.vde_checkbox('option', 'checkedClass');
    var checkedEventIsTriggered = false;
    var uncheckedEventIsTriggered = false;
    checkbox.on('checked.vde_checkbox', function() {checkedEventIsTriggered = true;});
    checkbox.on('unchecked.vde_checkbox', function() {uncheckedEventIsTriggered = true;});
    checkbox.trigger('click');
    assertEquals(true, checkbox.hasClass(checkedClass));
    assertEquals(true, checkedEventIsTriggered);
    assertEquals(false, uncheckedEventIsTriggered);
    checkbox.trigger('click');
    assertEquals(false, checkbox.hasClass(checkedClass));
    assertEquals(true, uncheckedEventIsTriggered);
    checkbox.vde_checkbox('destroy');
};
