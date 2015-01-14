/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "Magento_Catalog/js/price-utils",
    "jquery/ui"
], function($,utils){
    "use strict";

    var globalOptions = {
        fromSelector: 'form',
        dropdownsSelector: '[data-role=calendar-dropdown]'
    };


    $.widget('mage.priceOptionDate',{
        options: globalOptions,
        _create: initOptionDate
    });

    return $.mage.priceOptionDate;

    /**
     * Function-initializer of priceOptionDate widget
     */
    function initOptionDate() {
        /*jshint validthis: true */
        var field = this.element;
        var form = field.closest(this.options.fromSelector);
        var dropdowns = $(this.options.dropdownsSelector, field);
        var optionHandler = {};
        var dateOptionId;

        if(dropdowns.length) {
            dateOptionId = this.options.dropdownsSelector + dropdowns.attr('name');
            optionHandler.optionHandlers = {};
            optionHandler.optionHandlers[dateOptionId] = onCalendarDropdownChange(dropdowns);

            dropdowns.data('role', dateOptionId);

            form.priceOptions(optionHandler);
            dropdowns.on('change', onDateChange.bind(this, dropdowns));
        }
    }

    /**
     * Custom handler for Date-with-Dropdowns option type.
     * @param  {jQuery} siblings
     * @return {Function} function that return object { optionHash : optionAdditionalPrice }
     */
    function onCalendarDropdownChange (siblings) {
        return function(element, optionConfig, form) {
            var changes = {};
            var optionId = utils.findOptionId(event.target);
            var overhead = optionConfig[optionId].prices;
            var isNeedToUpdate = true;
            var optionHash = 'price-option-calendar-' + optionId;


            siblings.each(function(index, el){
                isNeedToUpdate = isNeedToUpdate && !!$(el).val();
            });

            overhead = isNeedToUpdate ? overhead : {};
            changes[optionHash] = overhead;

            return changes;
        };
    }

    /**
     * Adjusts the number of days in the day option element based on which month or year
     * is selected (changed). Adjusts the days to 28, 29, 30, or 31 typically.
     * @param {jQuery} dropdowns
     */
    function onDateChange(dropdowns) {
        var daysNodes,
            curMonth, curYear, expectedDays,
            options, needed;
        var month = dropdowns.filter('[data-calendar-role=month]');
        var year = dropdowns.filter('[data-calendar-role=year]');

        if(month.length && year.length) {
            daysNodes = dropdowns.filter('[data-calendar-role=day]').find('option');

            curMonth = month.val() || '01';
            curYear = year.val() || '2000';
            expectedDays = getDaysInMonth(curMonth, curYear);

            if(daysNodes.length - 1 > expectedDays) { // remove unnecessary option nodes
                daysNodes.each(function(i,e){
                    if(e.value > expectedDays) {
                        e.remove();
                    }
                });
            } else if(daysNodes.length - 1 < expectedDays) { // add missing option nodes
                options = [];
                needed = expectedDays - daysNodes.length + 1 ;
                while(needed--) {
                    options.push('<option value="' + (expectedDays - needed) + '">' + (expectedDays - needed) + '</option>');
                }
                $(options.join('')).insertAfter( daysNodes.last() );
            }
        }
    }

    /**
     * Returns number of days for special month and year
     * @param {number} month
     * @param {number} year
     * @return {number}
     */
    function getDaysInMonth(month, year) {
        return new Date(year, month, 0).getDate();
    }
});