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

            bindChangeHandlers: function(elements) {
                var self = this;
                var observableFields = shippingRatesValidationRules.getObservableFields();
                $.each(elements, function(index, elem) {
                    if (elem && observableFields.indexOf(elem.index) != -1) {
                        if (elem.index !== 'postcode') {
                            self.bindHandler(elem);
                        }
                    }

                    if (elem.index === 'postcode') {
                        self.bindHandler(elem);
                        postcodeElement = elem;
                    }
                });
            },

            bindHandler: function(element) {
                var self = this;
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
                        }, self.validateDelay);
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

                postcodeElement.error(null);
                if (!validationResult) {
                    var errorMessage = $t('Invalid Zip/Postal code for current country!');
                    if (postcodeValidator.validatedPostCodeExample.length) {
                        errorMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ');
                    }
                    postcodeElement.error(errorMessage);
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
