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
 * @category    mage.localization
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
LocalizeTest = TestCase('LocalizeTest');

LocalizeTest.prototype.testInit = function() {
    var localize = new mage.Localize('fr');
    assertEquals('fr', localize.name());
};

LocalizeTest.prototype.testDate = function() {
    var localize = new mage.Localize();
    assertEquals('6/7/2012', localize.date('06/07/2012 3:30 PM', 'd'));
    assertEquals('Thursday, June 07, 2012', localize.date('06/07/2012 3:30 PM', 'D'));
    assertEquals('Thursday, June 07, 2012 3:30 PM', localize.date('6/7/2012 3:30 PM', 'f'));
    assertEquals('Thursday, June 07, 2012 3:30:00 PM', localize.date('6/7/2012 3:30 PM', 'F'));
    assertEquals('June 07', localize.date('6/7/2012 3:30 PM', 'M'));
    assertEquals('2012-06-07T15:30:00', localize.date('6/7/2012 3:30 PM', 'S'));
    assertEquals('3:30 PM', localize.date('6/7/2012 3:30 PM', 't'));
    assertEquals('3:30:00 PM', localize.date('6/7/2012 3:30 PM', 'T'));
    assertEquals('2012 June', localize.date('6/7/2012 3:30 PM', 'Y'));
};

LocalizeTest.prototype.testNumber = function() {
    var localize = new mage.Localize();
    assertEquals('0.00', localize.number('0', 'n'));
};

LocalizeTest.prototype.testCurrency = function() {
    var localize = new mage.Localize();
    assertEquals('$0.00', localize.currency('0'));
};