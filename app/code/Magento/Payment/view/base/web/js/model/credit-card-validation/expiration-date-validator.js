/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mageUtils',
    'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/parse-date',
    'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-month-validator',
    'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-year-validator'
], function (utils, parseDate, expirationMonth, expirationYear) {
    'use strict';

    /**
     * @param {*} isValid
     * @param {*} isPotentiallyValid
     * @param {*} month
     * @param {*} year
     * @return {Object}
     */
    function resultWrapper(isValid, isPotentiallyValid, month, year) {
        return {
            isValid: isValid,
            isPotentiallyValid: isPotentiallyValid,
            month: month,
            year: year
        };
    }

    return function (value) {
        var date,
            monthValid,
            yearValid;

        if (utils.isEmpty(value)) {
            return resultWrapper(false, false, null, null);
        }

        value = value.replace(/^(\d\d) (\d\d(\d\d)?)$/, '$1/$2');
        date = parseDate(value);
        monthValid = expirationMonth(date.month);
        yearValid = expirationYear(date.year);

        if (monthValid.isValid && yearValid.isValid) {
            return resultWrapper(true, true, date.month, date.year);
        }

        if (monthValid.isPotentiallyValid && yearValid.isPotentiallyValid) {
            return resultWrapper(false, true, null, null);
        }

        return resultWrapper(false, false, null, null);
    };
});
