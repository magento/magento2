/**
 * Copyright © 2015 Magento. All rights reserved.
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
        'mage/translate'
    ],
    function ($, ko, shippingRatesValidationRules, addressConverter, selectShippingAddress, postcodeValidator, $t) {
        'use strict';
        var checkoutConfig = window.checkoutConfig;
        var validators = [];
        var observedElements = [];
        var postcodeElement = null;

        return {
            validateAddressTimeout: 0,
            validateDelay: 2000,

            registerValidator: function(carrier, validator) {
                if (checkoutConfig.activeCarriers.indexOf(carrier) != -1) {
                    validators.push(validator);
                }
            },

            validateAddressData: function(address) {
                return validators.some(function(validator) {
                    return validator.validate(address);
                });
            },

            bindChangeHandlers: function(elements, force, delay) {
                var self = this;
                var observableFields = shippingRatesValidationRules.getObservableFields();
                $.each(elements, function(index, elem) {
                    if (elem && (observableFields.indexOf(elem.index) != -1 || force)) {
                        if (elem.index !== 'postcode') {
                            self.bindHandler(elem, delay);
                        }
                    }

                    if (elem.index === 'postcode') {
                        self.bindHandler(elem, delay);
                        postcodeElement = elem;
                    }
                });
            },

            bindHandler: function(element, delay) {
                var self = this;
                delay = typeof delay === "undefined" ? self.validateDelay : delay;
                if (element.component.indexOf('/group') != -1) {
                    $.each(element.elems(), function(index, elem) {
                        self.bindHandler(elem);
                    });
                } else {
                    element.on('value', function() {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function() {
                            if (self.postcodeValidation()) {
                                self.validateFields();
                            }
                        }, delay);
                    });
                    observedElements.push(element);
                }
            },

            postcodeValidation: function() {
                if (postcodeElement == null || postcodeElement.value() == null) {
                    return true;
                }

                var countryId = $('select[name="shippingAddress[country_id]"]').val();
                var validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

                postcodeElement.warn(null);
                if (!validationResult) {
                    var warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');
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
            validateFields: function() {
                var addressFlat = addressConverter.formDataProviderToFlatData(
                    this.collectObservedData(),
                    'shippingAddress'
                );
                if (this.validateAddressData(addressFlat)) {
                    var address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                    selectShippingAddress(address);
                }
            },

            /**
             * Collect observed fields data to object
             *
             * @returns {*}
             */
            collectObservedData: function() {
                var observedValues = {};
                $.each(observedElements, function(index, field) {
                    observedValues[field.dataScope] = field.value();
                });
                return observedValues;
            }
        };
    }
);
