/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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