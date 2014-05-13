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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
DaterangeTest = TestCase('DaterangeTest');
DaterangeTest.prototype.testInit = function() {
    /*:DOC +=
     <div id="date-range" />
     */
    var dateRange = jQuery('#date-range').dateRange();
    assertEquals(true, dateRange.is(':mage-dateRange'));
    dateRange.dateRange('destroy');
};
DaterangeTest.prototype.testInitDateRangeDatepickers = function() {
    /*:DOC +=
     <div id="date-range">
         <input type="text" id="from" />
         <input type="text" id="to" />
     </div>
     */
    var options = {
            from: {
                id: "from"
            },
            to: {
                id: "to"
            }
        },
        dateRange = $('#date-range').dateRange(options),
        from = $('#'+options.from.id),
        to = $('#'+options.to.id);

    assertEquals(true, from.hasClass('hasDatepicker'));
    assertEquals(true, to.hasClass('hasDatepicker'));
    dateRange.dateRange('destroy');
};
DaterangeTest.prototype.testDestroy = function() {
    /*:DOC +=
     <div id="date-range">
     <input type="text" id="from" />
     <input type="text" id="to" />
     </div>
     */
    var options = {
        from: {
            id: "from"
        },
        to: {
            id: "to"
        }
    },
        dateRange = $('#date-range').dateRange(options),
        from = $('#'+options.from.id),
        to = $('#'+options.to.id),
        dateRangeExist = dateRange.is(':mage-dateRange'),
        fromExist = from.hasClass('hasDatepicker'),
        toExist = to.hasClass('hasDatepicker');

    dateRange.dateRange('destroy');
    assertEquals(true, dateRangeExist != dateRange.is(':mage-dateRange'));
    assertEquals(true, fromExist != from.hasClass('hasDatepicker'));
    assertEquals(true, toExist != to.hasClass('hasDatepicker'));
};