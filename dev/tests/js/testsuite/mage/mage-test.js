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
 * @category    mage.event
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
MageTest = TestCase('MageTest');
MageTest.prototype.testTrigger = function () {
    var observeFunc = function (e, o) {
        o.status = true;
    };
    $.mage.event.observe('mage.test.event', observeFunc);
    var obj = {status: false};
    assertEquals(false, obj.status);
    $.mage.event.trigger('mage.test.event', obj);
    assertEquals(true, obj.status);
    // Test removeObserver
    obj.status = false;
    assertEquals(false, obj.status);
    $.mage.event.removeObserver('mage.test.event', observeFunc);
    $.mage.event.trigger('mage.test.event', obj);
    assertEquals(false, obj.status);
};

MageTest.prototype.testLoad = function () {
    // Because the window load evnt already happened, syncQueue size already have 1 elements(the asyncLoad function)
    assertEquals(1, $.mage.load.js('test1'));
    assertEquals(1, $.mage.load.jsSync('test2'));
    assertEquals(1, $.mage.load.js('test1'));
    assertEquals(1, $.mage.load.jsSync('test2'));
};

MageTest.prototype.testLoadLanguage = function () {
    var mapping = {
        'localize': ['/pub/lib/mage/globalize/globalize.js',
            '/pub/lib/mage/globalize/cultures/globalize.culture.de.js',
            '/pub/lib/mage/localization/json/translate_de.js',
            '/pub/lib/mage/localization/localize.js']
    };
    assertEquals(1, $.mage.load.language('en', mapping));
    assertEquals(1, $.mage.load.language());
    assertEquals(5, $.mage.load.language('de', mapping));
};


