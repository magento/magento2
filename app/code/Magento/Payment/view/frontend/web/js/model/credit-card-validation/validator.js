/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'Magento_Payment/js/model/credit-card-validation/cvv-validator',
            'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-year-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-month-validator'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, cvvValidator, creditCardNumberValidator, expirationDateValidator, monthValidator) {
    "use strict";

    $.each({
        'validate-card-number': [
            /**
             * Validate credit card number based on mod 10
             * @param number - credit card number
             * @return {boolean}
             */
            function (number) {
                return creditCardNumberValidator(number).isValid;
            },
            'Please enter a valid credit card number11.'
        ],
        'validate-card-date': [
            /**
             * Validate credit card number based on mod 10
             * @param date - month
             * @return {boolean}
             */
                function (date) {
                return monthValidator(date).isValid;
            },
            'Incorrect credit card expiration date11.'
        ],
        'validate-card-cvv': [
            /**
             * Validate credit card number based on mod 10
             * @param cvv - month
             * @return {boolean}
             */
                function (cvv) {
                return cvvValidator(cvv).isValid;
            },
            'Please enter a valid credit card verification number11.'
        ]

    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
}));