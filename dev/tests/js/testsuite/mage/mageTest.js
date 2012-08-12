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
  mage.event.observe('test.event', function (e, o) {
    o.status = true;
  });
  var obj = {status: false};
  assertEquals(false, obj.status);
  mage.event.trigger('test.event', obj);
  assertEquals(true, obj.status);
};
MageTest.prototype.testLoad = function () {
  assertEquals(1, mage.load.js("test1"));
  assertEquals(1, mage.load.jsSync("test2"));
  assertEquals(1, mage.load.js("test1"));
  assertEquals(1, mage.load.jsSync("test2"));
};
MageTest.prototype.testLoadLanguage = function () {
  assertEquals(1, mage.load.language('en'));
  assertEquals(1, mage.load.language());
  assertEquals(5, mage.load.language('de'));
  var cookieName = 'language';
  $.cookie(cookieName, 'fr');
  assertEquals(7, mage.load.language());
  $.cookie(cookieName, null);
};


