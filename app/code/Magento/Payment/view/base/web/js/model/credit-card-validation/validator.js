/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'Magento_Payment/js/model/credit-card-validation/cvv-validator',
            'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-year-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-month-validator',
            'Magento_Payment/js/model/credit-card-validation/credit-card-data',
            'mage/translate'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, cvvValidator, creditCardNumberValidator, expirationDateValidator, monthValidator, creditCardData) {
    'use strict';

    $.each({
        'validate-card-type': [
            function (number, item, allowedTypes) {
                var cardInfo,
                    i,
                    l;

                if (!creditCardNumberValidator(number).isValid) {
                    return false;
                } else {
                    cardInfo = creditCardNumberValidator(number).card;

                    for (i = 0, l = allowedTypes.length; i < l; i++) {
                        if (cardInfo.title == allowedTypes[i].type) {
                            return true;
                        }
                    }

                    return false;
                }
            },
            $.mage.__('Please enter a valid credit card type number.')
        ],
        'validate-card-number': [

            /**
             * Validate credit card number based on mod 10
             * @param {String} number - credit card number
             * @return {Boolean}
             */
            function (number) {
                return creditCardNumberValidator(number).isValid;
            },
            $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-card-date': [

            /**
             * Validate credit card number based on mod 10
             * @param {String} date - month
             * @return {Boolean}
             */
            function (date) {
                return monthValidator(date).isValid;
            },
            $.mage.__('Incorrect credit card expiration month.')
        ],
        'validate-card-cvv': [

            /**
             * Validate credit card number based on mod 10
             * @param {String} cvv - month
             * @return {Boolean}
             */
            function (cvv) {
                var maxLength = creditCardData.creditCard ? creditCardData.creditCard.code.size : 3;

                return cvvValidator(cvv, maxLength).isValid;
            },
            $.mage.__('Please enter a valid credit card verification number.')
        ],
        'validate-card-year': [

            /**
             * Validate credit card number based on mod 10
             * @param {String} date - month
             * @return {Boolean}
             */
            function (date) {
                return monthValidator(date).isValid;
            },
            $.mage.__('Incorrect credit card expiration year.')
        ]

    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
}));
