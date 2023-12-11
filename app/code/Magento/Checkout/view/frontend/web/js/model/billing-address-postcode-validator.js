/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
           'jquery',
           'Magento_Checkout/js/model/postcode-validator',
           'mage/translate',
           'uiRegistry'
       ], function (
    $,
    postcodeValidator,
    $t,
    uiRegistry
) {
    'use strict';

    var postcodeElementName = 'postcode';

    return {
        validateZipCodeTimeout: 0,
        validateDelay: 2000,

        /**
         * Perform postponed binding for fieldset elements
         *
         * @param {String} formPath
         */
        initFields: function (formPath) {
            var self = this;

            uiRegistry.async(formPath + '.' + postcodeElementName)(self.bindHandler.bind(self));
        },

        /**
         * @param {Object} element
         * @param {Number} delay
         */
        bindHandler: function (element, delay) {
            var self = this;

            delay = typeof delay === 'undefined' ? self.validateDelay : delay;

            element.on('value', function () {
                clearTimeout(self.validateZipCodeTimeout);
                self.validateZipCodeTimeout = setTimeout(function () {
                    self.postcodeValidation(element);
                }, delay);
            });
        },

        /**
         * @param {Object} postcodeElement
         * @return {*}
         */
        postcodeValidation: function (postcodeElement) {
            var countryId = $('select[name="country_id"]:visible').val(),
                validationResult,
                warnMessage;

            if (postcodeElement == null || postcodeElement.value() == null) {
                return true;
            }

            postcodeElement.warn(null);
            validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

            if (!validationResult) {
                warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                if (postcodeValidator.validatedPostCodeExample.length) {
                    warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                }
                warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                postcodeElement.warn(warnMessage);
            }

            return validationResult;
        }
    };
});
