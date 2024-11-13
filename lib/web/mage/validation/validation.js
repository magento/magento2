/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/validation',
    'mage/translate'
], function ($) {
    'use strict';

    $.each({
        'validate-grouped-qty': [
            function (value, element, params) {
                var result = false,
                    total = 0;

                $(params).find('input[data-validate*="validate-grouped-qty"]').each(function (i, e) {
                    var val = $(e).val(),
                        valInt;

                    if (val && val.length > 0) {
                        result = true;
                        valInt = parseFloat(val) || 0;

                        if (valInt >= 0) {
                            total += valInt;
                        } else {
                            result = false;

                            return result;
                        }
                    }
                });

                return result && total > 0;
            },
            $.mage.__('Please specify the quantity of product(s).')
        ],
        'validate-one-checkbox-required-by-name': [
            function (value, element, params) {
                var checkedCount = 0,
                    container;

                if (element.type === 'checkbox') {
                    $('[name="' + element.name + '"]').each(
                        function () {
                            if ($(this).is(':checked')) {
                                checkedCount += 1;

                                return false;
                            }
                        }
                    );
                }
                container = '#' + params;

                if (checkedCount > 0) {
                    $(container).removeClass('validation-failed');
                    $(container).addClass('validation-passed');

                    return true;
                }
                $(container).addClass('validation-failed');
                $(container).removeClass('validation-passed');

                return false;
            },
            $.mage.__('Please select one of the options.')
        ],
        'validate-date-between': [
            function (value, element, params) {
                var minDate = new Date(params[0]),
                    maxDate = new Date(params[1]),
                    inputDate = new Date(element.value),
                    message;

                minDate.setHours(0);
                maxDate.setHours(0);

                if (inputDate >= minDate && inputDate <= maxDate) {
                    return true;
                }
                message = $.mage.__('Please enter a date between %min and %max.');
                this.dateBetweenErrorMessage = message.replace('%min', minDate).replace('%max', maxDate);

                return false;
            },
            function () {
                return this.dateBetweenErrorMessage;
            }
        ],
        'validate-dob': [
            function (val, element, params) {
                var dob = $(element).parents('.customer-dob'),
                    dayVal, monthVal, yearVal, dobLength, day, month, year, curYear,
                    validYearMessage, validateDayInMonth, validDateMessage, today, dateEntered;

                $(dob).find('.' + this.settings.errorClass).removeClass(this.settings.errorClass);
                dayVal = $(dob).find(params[0]).find('input:text').val();
                monthVal = $(dob).find(params[1]).find('input:text').val();
                yearVal = $(dob).find(params[2]).find('input:text').val();
                dobLength = dayVal.length + monthVal.length + yearVal.length;

                if (params[3] && dobLength === 0) {
                    this.dobErrorMessage = $.mage.__('This is a required field.');

                    return false;
                }

                if (!params[3] && dobLength === 0) {
                    return true;
                }
                day = parseInt(dayVal, 10) || 0;
                month = parseInt(monthVal, 10) || 0;
                year = parseInt(yearVal, 10) || 0;
                curYear = new Date().getFullYear();

                if (!day || !month || !year) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid full date.');

                    return false;
                }

                if (month < 1 || month > 12) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid month (1-12).');

                    return false;
                }

                if (year < 1900 || year > curYear) {
                    validYearMessage = $.mage.__('Please enter a valid year (1900-%1).');
                    this.dobErrorMessage = validYearMessage.replace('%1', curYear.toString());

                    return false;
                }
                validateDayInMonth = new Date(year, month, 0).getDate();

                if (day < 1 || day > validateDayInMonth) {
                    validDateMessage = $.mage.__('Please enter a valid day (1-%1).');
                    this.dobErrorMessage = validDateMessage.replace('%1', validateDayInMonth.toString());

                    return false;
                }
                today = new Date();
                dateEntered = new Date();
                dateEntered.setFullYear(year, month - 1, day);

                if (dateEntered > today) {
                    this.dobErrorMessage = $.mage.__('Please enter a date from the past.');

                    return false;
                }

                day = day % 10 === day ? '0' + day : day;
                month = month % 10 === month ? '0' + month : month;
                $(element).val(month + '/' + day + '/' + year);

                return true;
            },
            function () {
                return this.dobErrorMessage;
            }
        ]
    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
});
