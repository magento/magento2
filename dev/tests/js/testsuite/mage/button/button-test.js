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
 * @category    mage.js
 * @package     test
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
ButtonTest = TestCase('ButtonTest');
ButtonTest.prototype.testInit = function() {
    /*:DOC += <button id="test-button"></button><div id="event-target"></div>*/
    assertTrue(jQuery('#test-button').button().is(':ui-button'));
};
ButtonTest.prototype.testProcessDataAttr = function() {
    /*:DOC += <button id="test-button" data-widget-button="{&quot;event&quot;:&quot;testEvent&quot;,&quot;related&quot;:&quot;#event-target&quot;}"></button>
        <div id="event-target"></div>*/
    var button = jQuery('#test-button').button();
    assertEquals('testEvent', button.button('option', 'event'));
    assertEquals('#event-target', button.button('option', 'related'));
};
ButtonTest.prototype.testBind = function() {
    /*:DOC += <button id="test-button" data-widget-button="{&quot;event&quot;:&quot;testEvent&quot;,&quot;related&quot;:&quot;#event-target&quot;}"></button>
        <div id="event-target"></div>*/
    var testEventTriggered = false;
    jQuery('#event-target').on('testEvent', function(e) {
        testEventTriggered = true;
    });
    jQuery('#test-button').button().click();

    assertTrue(testEventTriggered);
};
