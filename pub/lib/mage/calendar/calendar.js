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
 * @category    frontend calendar
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*global document:true jQuery:true*/
(function ($) {
    /*
     * Full documentation for the datepicker, including all options, APIs, and events can be found
     * here: http://docs.jquery.com/UI/Datepicker. Note that all of the required options must be set by
     * the initialization event.
     */
    var datepickerOptions = {
        buttonImage: null, /* The URL for the calendar icon. Reguired. */
        buttonImageOnly: true, /* The buttonImage is the trigger. Displays image, but not button. */
        buttonText: null, /* Text displayed when hovering over buttonImage. Required.*/
        changeMonth: true, /* Dropdown selectable month. */
        changeYear: true, /* Dropdown selectable year, if yearRange includes multiple years. */
        showButtonPanel: true, /* Show the Today and Done buttons. */
        showOn: 'button', /* The datepicker only appears when the buttonImage is clicked. */
        showWeek: true, /* Show the week of the year column. */
        yearRange: null /* The year range. Defaults to current year + or - 10 years. Required. */
        /* The required format for the yearRange option is ####:#### (e.g. 2012:2015). */
    };

    $(document).ready(function () {
        var calendarInit = {
            datepicker: [] /* Array of datepickers. Possibly more than one on any given page. */
        };
        $.mage.event.trigger("mage.calendar.initialize", calendarInit);
        $.each(calendarInit.datepicker, function (index, value) {
            $(value.inputSelector).datepicker(
                /* Merge datepicker options. Include localized settings which may default to English. */
                $.extend(datepickerOptions, $.datepicker.regional[value.locale], value.options)
            );
        });
    });

})(jQuery);
