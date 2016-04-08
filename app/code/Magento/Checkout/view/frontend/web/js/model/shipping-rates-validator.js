/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        './shipping-rates-validation-rules',
        '../model/address-converter',
        '../action/select-shipping-address',
        './postcode-validator',
        './shipping-rate-registry',
        'mage/translate'
    ],
    function ($, ko, shippingRatesValidationRules, addressConverter, selectShippingAddress, postcodeValidator, shippingRatesRegistry, $t) {
        'use strict';

        var checkoutConfig = window.checkoutConfig;
        var OBSERVED_ELEMENTS = 'shipping-rates-validator:observedElements';
        var POSTCODE_ELEMENT = 'shipping-rates-validator:postcode_element';
        var VALIDATORS = 'shipping-rates-validator:validators';

        return {
            validateAddressTimeout: 0,
            validateDelay: 2000,

            /**
             * @param {String} carrier
             * @param {Object} validator
             */
            registerValidator: function (carrier, validator) {
                if (checkoutConfig.activeCarriers.indexOf(carrier) != -1) {
                    var validators = shippingRatesRegistry.get(VALIDATORS) || [];
                    validators.push(validator);
                    shippingRatesRegistry.set(VALIDATORS, validators);
                }
            },

            /**
             * @param {Object} address
             * @return {Boolean}
             */
            validateAddressData: function (address) {
                var validators = shippingRatesRegistry.get(VALIDATORS);
                return validators.some(function (validator) {
                    return validator.validate(address);
                });
            },

            /**
             * @param {*} elements
             * @param {Boolean} force
             * @param {Number} delay
             */
            bindChangeHandlers: function (elements, force, delay) {
                var self = this,
                    observableFields = shippingRatesValidationRules.getObservableFields();

                $.each(elements, function (index, elem) {
                    if (elem && (observableFields.indexOf(elem.index) !== -1 || force)) {
                        if (elem.index !== 'postcode') {
                            self.bindHandler(elem, delay);
                        }
                    }

                    if (elem.index === 'postcode') {
                        self.bindHandler(elem, delay);
                        shippingRatesRegistry.set(POSTCODE_ELEMENT, elem);
                    }
                });
            },

            /**
             * @param {Object} element
             * @param {Number} delay
             */
            bindHandler: function (element, delay) {
                var self = this,
                    observedElements = shippingRatesRegistry.get(OBSERVED_ELEMENTS) || [];

                delay = typeof delay === 'undefined' ? self.validateDelay : delay;

                if (element.component.indexOf('/group') !== -1) {
                    $.each(element.elems(), function (index, elem) {
                        self.bindHandler(elem);
                    });
                } else {
                    element.on('value', function () {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function () {
                            if (self.postcodeValidation()) {
                                self.validateFields();
                            }
                        }, delay);
                    });
                    observedElements.push(element);
                    shippingRatesRegistry.set(OBSERVED_ELEMENTS, observedElements);
                }
            },

            /**
             * @return {*}
             */
            postcodeValidation: function () {
                var countryId = $('select[name="country_id"]').val(),
                    postcodeElement = shippingRatesRegistry.get(POSTCODE_ELEMENT),
                    validationResult = postcodeValidator.validate(postcodeElement.value(), countryId),
                    warnMessage;

                if (postcodeElement == null || postcodeElement.value() == null) {
                    return true;
                }

                postcodeElement.warn(null);

                if (!validationResult) {
                    warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                    if (postcodeValidator.validatedPostCodeExample.length) {
                        warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                    }
                    warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                    postcodeElement.warn(warnMessage);
                }

                return validationResult;
            },

            /**
             * Convert form data to quote address and validate fields for shipping rates
             */
            validateFields: function () {
                var addressFlat = addressConverter.formDataProviderToFlatData(
                        this.collectObservedData(),
                        'shippingAddress'
                    ),
                    address;

                if (this.validateAddressData(addressFlat)) {
                    address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                    selectShippingAddress(address);
                }
            },

            /**
             * Collect observed fields data to object
             *
             * @returns {*}
             */
            collectObservedData: function () {
                var observedValues = {},
                    observedElements = shippingRatesRegistry.get(OBSERVED_ELEMENTS);

                $.each(observedElements, function (index, field) {
                    observedValues[field.dataScope] = field.value();
                });

                return observedValues;
            }
        };
    }
);
