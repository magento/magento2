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
 * @category    mage.calendar
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
CalendarTest = TestCase('CalendarTest');
CalendarTest.prototype.testCalendar = function () {
    /*:DOC +=
        <div>
            <input type="text" id="datepicker"/>
            <script type="text/javascript">
                //<![CDATA[
                $.mage.event.observe("mage.calendar.initialize", function (event, initData) {
                    var datepicker = {
                        inputSelector: "#datepicker",
                        locale: "",
                        options: {
                            buttonImage: "",
                            buttonText: "Select Date",
                            dateFormat: "mm-dd-yy",
                            yearRange: "2012:2015"
                        }
                    };
                    initData.datepicker.push(datepicker);
                });
                //]]>
            </script>
            <script type="text/javascript" src="/pub/lib/mage/calendar/calendar.js"></script>
        </div>
    */

    var datepicker = $.datepicker._getInst($('#datepicker')[0]);
    assertNotUndefined(datepicker);

    assertEquals("Select Date", datepicker.settings.buttonText);
    assertEquals("mm-dd-yy", datepicker.settings.dateFormat);
    assertEquals("button", datepicker.settings.showOn);
    assertEquals("2012:2015", datepicker.settings.yearRange);
};

