/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.dateOption', {
        options: {
        },

        _create: function() {
            $(this.options.datepickerFieldSelector)
                .on('change', $.proxy(function() {this.element.trigger('reloadPrice');}, this));
            $(this.options.monthSelector)
                .on('change', $.proxy(function(event) {this._reloadMonth(event);}, this));
            $(this.options.yearSelector)
                .on('change', $.proxy(function(event) {this._reloadMonth(event);}, this));
        },

        /**
         * Calculates the total number of days in the specified month in the specified year.
         * Can be between 1-31 depending on the month (e.g. usually 28, 29, 30, or 31).
         * @private
         * @param month Numerical value of the month (e.g. 1-12)
         * @param year Numerical value of the year (e.g. 2012)
         * @return {Number} The number of days in the month of the year (e.g. 1-31)
         */
        _getDaysInMonth: function(month, year)
        {
            return new Date(year, month, 0).getDate();
        },

        /**
         * Adds a new DOM option element to the given selector, typically a day selector. This
         * is used for adjusting the number of available options (e.g. days of the month) based
         * on which month and year has been chosen.
         * @private
         * @param select A select element, usually for the number of days in the month.
         * @param text Text value that represents the numerical day for the new option.
         * @param value Value that represents the numerical day for the new option.
         */
        _addOption: function(select, text, value)
        {
            var option = document.createElement('OPTION');
            option.value = value;
            option.text = text;

            if (select.options.add) {
                select.options.add(option);
            } else {
                select.appendChild(option);
            }
        },

        /**
         * Adjusts the number of days in the day option element based on which month or year
         * is selected (changed). Adjusts the days to 28, 29, 30, or 31 typically.
         * @private
         * @param event Event from an .on('change') for the month and year select elements.
         * @return {(null|Boolean}} Returns false if the select element doesn't contain the
         * right number of parts. Otherwise returns nothing.
         */
        _reloadMonth: function(event) {
            var selectEl = $(event.target),
                idParts = selectEl.attr('id').split("_");

            if (idParts.length !== 3) {
                return false;
            }

            var optionIdPrefix = "#" + idParts[0] + "_" + idParts[1],
                month = parseInt($(optionIdPrefix + "_month").val(), 10),
                year = parseInt($(optionIdPrefix + "_year").val(), 10),
                dayEl = $(optionIdPrefix + "_day")[0],
                days = this._getDaysInMonth(month, year);

            for (var i = dayEl.length - 1; i >= 0; i--) {
                if (dayEl.options[i].value > days) {
                    dayEl.remove(dayEl.options[i].index);
                }
            }

            var lastDay = parseInt(dayEl.options[dayEl.length-1].value, 10);
            for (i = lastDay + 1; i <= days; i++) {
                this._addOption(dayEl, i, i);
            }
        }
    });
    
    return $.mage.dateOption;
});