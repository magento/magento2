/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Braintree/js/view/payment/adapter',
        'braintreeHostedFields',
        'Magento_Checkout/js/model/quote',
        'Magento_Braintree/js/validator',
        'Magento_Ui/js/model/messageList',
        'Magento_Braintree/js/view/payment/validator-handler',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Braintree/js/view/payment/kount',
        'mage/translate',
        'domReady!'
    ],
    function (
        _,
        $,
        Component,
        braintreeAdapter,
        hostedFields,
        quote,
        validator,
        globalMessageList,
        validatorManager,
        VaultEnabler,
        kount,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Braintree/payment/form',
                active: false,
                code: 'braintree',
                lastBillingAddress: null,
                hostedFieldsInstance: null,
                selectorsMapper: {
                    'expirationMonth': 'expirationMonth',
                    'expirationYear': 'expirationYear',
                    'number': 'cc_number',
                    'cvv': 'cc_cid'
                },
                paymentPayload: {
                    nonce: null
                },
                additionalData: {}
            },

            /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                var self = this;

                self._super();
                self.vaultEnabler = new VaultEnabler();
                self.vaultEnabler.setPaymentCode(self.getVaultCode());

                kount.getDeviceData()
                    .then(function (deviceData) {
                        self.additionalData['device_data'] = deviceData;
                    });

                return self;
            },

            /**
             * Init hosted fields.
             *
             * Is called after knockout finishes input fields bindings.
             */
            initHostedFields: function () {
                var self = this;

                braintreeAdapter.getApiClient()
                    .then(function (clientInstance) {

                        return hostedFields.create({
                            client: clientInstance,
                            fields: self.getFieldsConfiguration()
                        });
                    })
                    .then(function (hostedFieldsInstance) {
                        self.hostedFieldsInstance = hostedFieldsInstance;

                        if ($('#billing-address-same-as-shipping-braintree').is(':checked')) {
                            self.isPlaceOrderActionAllowed(true);
                        } else {
                            self.isPlaceOrderActionAllowed(false);
                        }
                        self.initFormValidationEvents(hostedFieldsInstance);

                        return self.hostedFieldsInstance;
                    })
                    .catch(function () {
                        self.showError($t('Payment ' + self.getTitle() + ' can\'t be initialized'));
                    });
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active']);

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentPayload.nonce
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {
                    return validator.collectTypes(
                        availableTypes,
                        validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * @returns {Boolean}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            /**
             * Returns vault code.
             *
             * @returns {String}
             */
            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            /**
             * Action to place order
             * @param {String} key
             */
            placeOrder: function (key) {
                var self = this;

                if (key) {
                    return self._super();
                }
                // place order on success validation
                validatorManager.validate(self, function () {
                    return self.placeOrder('parent');
                }, function (err) {

                    if (err) {
                        self.showError(err);
                    }
                });

                return false;
            },

            /**
             * Returns state of place order button
             *
             * @returns {Boolean}
             */
            isButtonActive: function () {
                return this.isActive() && this.isPlaceOrderActionAllowed();
            },

            /**
             * Trigger order placing
             */
            placeOrderClick: function () {
                var self = this;

                if (this.isFormValid(this.hostedFieldsInstance)) {
                    self.hostedFieldsInstance.tokenize(function (err, payload) {
                        if (err) {
                            self.showError($t('Some payment input fields are invalid.'));

                            return;
                        }

                        if (self.validateCardType()) {
                            self.setPaymentPayload(payload);
                            self.placeOrder();
                        }
                    });
                }
            },

            /**
             * Validates credit card form.
             *
             * @param {Object} hostedFieldsInstance
             * @returns {Boolean}
             * @private
             */
            isFormValid: function (hostedFieldsInstance) {
                var self = this,
                    state = hostedFieldsInstance.getState();

                return Object.keys(state.fields).every(function (fieldKey) {
                    if (fieldKey in self.selectorsMapper && state.fields[fieldKey].isValid === false) {
                        self.addInvalidClass(self.selectorsMapper[fieldKey]);
                    }

                    return state.fields[fieldKey].isValid;
                });
            },

            /**
             * Init form validation events.
             *
             * @param {Object} hostedFieldsInstance
             * @private
             */
            initFormValidationEvents: function (hostedFieldsInstance) {
                var self = this;

                hostedFieldsInstance.on('empty', function (event) {
                    if (event.emittedBy === 'number') {
                        self.selectedCardType(null);
                    }

                });

                hostedFieldsInstance.on('blur', function (event) {
                    if (event.emittedBy === 'number') {
                        self.validateCardType();
                    }
                });

                hostedFieldsInstance.on('validityChange', function (event) {
                    var field = event.fields[event.emittedBy],
                        fieldKey = event.emittedBy;

                    if (fieldKey === 'number') {
                        self.isValidCardNumber = field.isValid;
                    }

                    if (fieldKey in self.selectorsMapper && field.isValid === false) {
                        self.addInvalidClass(self.selectorsMapper[fieldKey]);
                    }
                });

                hostedFieldsInstance.on('cardTypeChange', function (event) {
                    if (event.cards.length === 1) {
                        self.selectedCardType(
                            validator.getMageCardType(event.cards[0].type, self.getCcAvailableTypes())
                        );
                    }
                });
            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             * @private
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Add invalid class to field.
             *
             * @param {String} field
             * @returns void
             * @private
             */
            addInvalidClass: function (field) {
                $(this.getSelector(field)).addClass('braintree-hosted-fields-invalid');
            },

            /**
             * Remove invalid class from field.
             *
             * @param {String} field
             * @returns void
             * @private
             */
            removeInvalidClass: function (field) {
                $(this.getSelector(field)).removeClass('braintree-hosted-fields-invalid');
            },

            /**
             * Get Braintree Hosted Fields
             *
             * @returns {Object}
             * @private
             */
            getFieldsConfiguration: function () {
                var self = this,
                    fields = {
                        number: {
                            selector: self.getSelector('cc_number')
                        },
                        expirationMonth: {
                            selector: self.getSelector('expirationMonth'),
                            placeholder: $t('MM')
                        },
                        expirationYear: {
                            selector: self.getSelector('expirationYear'),
                            placeholder: $t('YY')
                        }
                    };

                if (self.hasVerification()) {
                    fields.cvv = {
                        selector: self.getSelector('cc_cid')
                    };
                }

                return fields;
            },

            /**
             * Validate current credit card type.
             *
             * @returns {Boolean}
             * @private
             */
            validateCardType: function () {
                var cardFieldName = 'cc_number';

                this.removeInvalidClass(cardFieldName);

                if (this.selectedCardType() === null || !this.isValidCardNumber) {
                    this.addInvalidClass(cardFieldName);

                    return false;
                }

                return true;
            },

            /**
             * Sets payment payload
             *
             * @param {Object} paymentPayload
             * @private
             */
            setPaymentPayload: function (paymentPayload) {
                this.paymentPayload = paymentPayload;
            },

            /**
             * Show error message
             *
             * @param {String} errorMessage
             * @private
             */
            showError: function (errorMessage) {
                globalMessageList.addErrorMessage({
                    message: errorMessage
                });
            }
        });
    }
);
