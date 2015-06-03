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
        '../action/select-shipping-address'
    ],
    function ($, ko, shippingRatesValidationRules, addressConverter, selectShippingAddress) {
        "use strict";
        return {
            validators: [],
            validateAddressTimeout: 0,
            validateDelay: 3000,
            observedElements: [],

            registerValidator: function(validator) {
                this.validators.push(validator);
            },

            validateAddress: function(address) {
                var valid = false;
                $.each(this.validators, function(index, validator) {
                    var result = validator.validate(address);
                    if (result) {
                        valid = true;
                        return false;
                    }
                });
                return valid;
            },

            bindChangeHandlers: function(elements) {
                var self = this;
                var observableFields = shippingRatesValidationRules.getObservableFields();
                $.each(elements, function(index, elem) {
                    if (elem && $.inArray(elem.index, observableFields) != -1) {
                        self.bindHandler(elem);
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
                    $('#checkout').on('keyup change', '#' + element.uid, function(event) {
                        if ($(this).is('input') && event.type == 'change') {
                            return false;
                        }
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function() {
                            self.validateFields();
                        }, self.validateDelay);
                    });
                    self.observedElements.push(element);
                }
            },

            validateFields: function() {
                var address = addressConverter.formDataProviderToQuoteAddress(
                    this.collectObservedData(),
                    'shippingAddress'
                );
                if (this.validateAddress(address)) {
                    selectShippingAddress(ko.observable(address));
                }
            },

            collectObservedData: function() {
                var observedValues = {};
                $.each(this.observedElements, function(index, field) {
                    var fieldSelector = '#' + field.uid;
                    observedValues[field.dataScope] = $(fieldSelector).val();
                });
                return observedValues;
            }
        };
    }
);
