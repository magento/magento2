/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([], function () {
    'use strict';

    var validators = [];

    return {
        /**
         * Register unique validator
         *
         * @param {*} validator
         */
        registerValidator: function (validator) {
            validators.push(validator);
        },

        /**
         * Returns array of registered validators
         *
         * @returns {Array}
         */
        getValidators: function () {
            return validators;
        },

        /**
         * Process validators
         *
         * @returns {Boolean}
         */
        validate: function (hideError) {
            var validationResult = true;

            hideError = hideError || false;

            if (validators.length <= 0) {
                return validationResult;
            }

            validators.forEach(function (item) {
                if (item.validate(hideError) == false) { //eslint-disable-line eqeqeq
                    validationResult = false;

                    return false;
                }
            });

            return validationResult;
        }
    };
});
