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
 * @category    validation
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    "use strict";

    /**
     * Javascript object with credit card types
     * 0 - regexp for card number
     * 1 - regexp for cvn
     * 2 - check or not credit card number trough Luhn algorithm by
     */
    var creditCartTypes = {
        'SO': [new RegExp('^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
        'SM': [new RegExp('(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|(^(4936)([0-9]{12}$|[0-9]{14,15}$))'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
        'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
        'MC': [new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$'), true],
        'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
        'DI': [new RegExp('^6011[0-9]{12}$'), new RegExp('^[0-9]{3}$'), true],
        'JCB': [new RegExp('^(3[0-9]{15}|(2131|1800)[0-9]{11})$'), new RegExp('^[0-9]{3,4}$'), true],
        'OT': [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), false]
    };

    function validateCreditCard(s) {
        // remove non-numerics
        var v = "0123456789",
            w = "", i, j, k, m, c, a, x;
        for (i = 0; i < s.length; i++) {
            x = s.charAt(i);
            if (v.indexOf(x, 0) != -1)
                w += x;
        }
        // validate number
        j = w.length / 2;
        k = Math.floor(j);
        m = Math.ceil(j) - k;
        c = 0;
        for (i = 0; i < k; i++) {
            a = w.charAt(i * 2 + m) * 2;
            c += a > 9 ? Math.floor(a / 10 + a % 10) : a;
        }
        for (i = 0; i < k + m; i++) {
            c += w.charAt(i * 2 + 1 - m) * 1;
        }
        return (c % 10 === 0);
    }

    /**
     * Validation rule for grouped product, with multiple qty fields,
     * only one qty needs to have a positive integer
     */
    $.validator.addMethod(
        "validate-grouped-qty",
        function(value, element, params) {
            var result = false;
            var total = 0;
            $(params).find('input[data-validate*="validate-grouped-qty"]').each(function(i, e) {
                var val = $(e).val();
                if (val && val.length > 0) {
                    result = true;
                    var valInt = parseInt(val, 10) || 0;
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
        'Please specify the quantity of product(s).'
    );

    $.validator.addMethod(
        "validate-one-checkbox-required-by-name",
        function(value, element, params) {
            var checkedCount = 0;
            if (element.type === 'checkbox') {
                $('[name="' + element.name + '"]').each(function() {
                    if ($(this).is(':checked')) {
                        checkedCount += 1;
                        return false;
                    }
                });
            }
            var container = '#' + params;
            if (checkedCount > 0) {
                $(container).removeClass('validation-failed');
                $(container).addClass('validation-passed');
                return true;
            } else {
                $(container).addClass('validation-failed');
                $(container).removeClass('validation-passed');
                return false;
            }
        },
        'Please select one of the options.'
    );

    $.validator.addMethod(
        "validate-date-between",
        function(value, element, params) {
            var minDate = new Date(params[0]),
                maxDate = new Date(params[1]),
                inputDate = new Date(element.value);
            minDate.setHours(0);
            maxDate.setHours(0);
            if (inputDate >= minDate && inputDate <= maxDate) {
                return true;
            }
            this.dateBetweenErrorMessage = $.mage.__('Please enter a date between %min and %max.').replace('%min', minDate).replace('%max', maxDate);
            return false;
        },
        function(){
            return this.dateBetweenErrorMessage;
        }
    );

    $.validator.addMethod(
        "validate-cc-type-select",
        /**
         * Validate credit card type matches credit card number
         * @param value - select credit card type
         * @param element - element contains the select box for credit card types
         * @param params - selector for credit card number
         * @return {boolean}
         */
        function(value, element, params) {
            if (value && params && creditCartTypes[value]) {
                return creditCartTypes[value][0].test($(params).val());
            }
            return false;
        },
        'Card type does not match credit card number.'
    );

    $.validator.addMethod(
        "validate-cc-number",
        /**
         * Validate credit card number based on mod 10
         * @param value - credit card number
         * @return {boolean}
         */
        function(value) {
            if (value) {
                return validateCreditCard(value);
            }
            return false;
        },
        'Please enter a valid credit card number.'
    );

    $.validator.addMethod(
        "validate-cc-type",
        /**
         * Validate credit card number is for the currect credit card type
         * @param value - credit card number
         * @param element - element contains credit card number
         * @param params - selector for credit card type
         * @return {boolean}
         */
        function(value, element, params) {
            if (value && params) {
                var ccType = $(params).val();
                value = value.replace(/\s/g, '').replace(/\-/g, '');
                if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                    return creditCartTypes[ccType][0].test(value);
                } else if (!creditCartTypes[ccType][0]) {
                    return true;
                }
            }
            return false;
        },
        'Credit card number does not match credit card type.'
    );

    $.validator.addMethod(
        "validate-cc-exp",
        /**
         * Validate credit card expiration date, make sure it's within the year and not before current month
         * @param value - month
         * @param element - element contains month
         * @param params - year selector
         * @return {Boolean}
         */
        function(value, element, params) {
            if (value && params) {
                var month = value,
                    year = $(params).val(),
                    currentTime  = new Date(),
                    currentMonth = currentTime.getMonth() + 1,
                    currentYear  = currentTime.getFullYear();
                if (!year) {
                    return true;
                }
                if (year >= currentYear && month >= currentMonth) {
                    return true;
                }
            }
            return false;
        },
        'Incorrect credit card expiration date.'
    );

    $.validator.addMethod(
        "validate-cc-cvn",
        /**
         * Validate credit card cvn based on credit card type
         * @param value - credit card cvn
         * @param element - element contains credit card cvn
         * @param params - credit card type selector
         * @return {*}
         */
        function(value, element, params) {
            if (value && params) {
                var ccType = $(params).val();
                if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                    return creditCartTypes[ccType][1].test(value);
                }
            }
            return false;
        },
        'Please enter a valid credit card verification number.'
    );
})(jQuery);
