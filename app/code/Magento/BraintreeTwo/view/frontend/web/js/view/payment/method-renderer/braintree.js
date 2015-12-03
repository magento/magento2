/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'mage/translate',
        'Magento_BraintreeTwo/js/validator'
    ],
    function (
        $,
        Component,
        globalMessageList,
        quote,
        $t,
        validator
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_BraintreeTwo/payment/form',
                active: false,
                scriptLoaded: false,
                braintreeClient: null,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * Set list of observable attributes
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);

                this._super()
                    .observe('active scriptLoaded');

                return this;
            },

            /**
             * Get payment name
             * @returns {String}
             */
            getCode: function () {
                return 'braintreetwo';
            },

            /**
             * Get full selector name
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Check if payment is active
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            /**
             * Triggers on payment change
             * @param {Boolean} isActive
             */
            onActiveChange: function (isActive) {
                if (!isActive) {
                    return;
                }

                if (this.getClientToken()) {
                    if (!this.scriptLoaded()) {
                        this.loadScript();
                    }
                } else {
                    globalMessageList.addErrorMessage({
                        'message': $t('Sorry, but something went wrong')
                    });
                }
            },

            /**
             * Load Braintree SDK
             */
            loadScript: function () {
                var state = this.scriptLoaded,
                    self = this;

                $('body').trigger('processStart');
                require([this.getSdkUrl()], function (braintree) {
                    state(true);
                    self.braintreeClient = braintree;
                    self.initBraintree();
                    $('body').trigger('processStop');
                });
            },

            /**
             * Init Braintree client
             */
            initBraintree: function () {
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
                            placeholder: $t('YYYY')
                        },

                        /**
                         * Triggers on Hosted Field changes
                         * @param {Object} event
                         * @returns {Boolean}
                         */
                        onFieldEvent: function (event) {
                            if (event.isEmpty === false) {
                                self.validateCardType();
                            }

                            if (event.type === 'fieldStateChange') {
                                // Handle a change in validation or card type
                                self.selectedCardType(null);

                                if (!event.isPotentiallyValid && !event.isValid) {
                                    return false;
                                }

                                if (event.card) {
                                    self.selectedCardType(
                                        validator.getMageCardType(event.card.type, self.getCcAvailableTypes())
                                    );
                                }
                            }
                        }
                    };

                if (self.hasVerification()) {
                    fields.cvv = {
                        selector: self.getSelector('cc_cid')
                    };
                }

                this.braintreeClient.setup(this.getClientToken(), 'custom', {
                    id: 'co-transparent-form-braintree',
                    hostedFields: fields,

                    /**
                     * Triggers on payment nonce receive
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        self.paymentMethodNonce = response.nonce;
                        self.placeOrder();
                    },

                    /**
                     * Triggers on any Braintree error
                     * @param {Object} response
                     */
                    onError: function (response) {
                        self.messageContainer.addErrorMessage({
                            'message': response.message
                        });
                    }
                });
            },

            /**
             * Validate current credit card type
             * @returns {Boolean}
             */
            validateCardType: function () {
                if (this.selectedCardType() === null) {
                    $(this.getSelector('cc_number')).attr('class', 'braintree-hosted-fields-invalid');

                    return false;
                }

                return true;
            },

            /**
             * Get url of Braintree SDK
             * @returns {String}
             */
            getSdkUrl: function () {

                return window.checkoutConfig.payment[this.getCode()].sdkUrl;
            },

            /**
             * Get client token
             * @returns {String|*}
             */
            getClientToken: function () {

                return window.checkoutConfig.payment[this.getCode()].clientToken;
            },

            /**
             * Get list of available CC types
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
                        availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };
            },

            /**
             * Trigger order placing
             */
            placeOrderClick: function () {
                if (this.validateCardType()) {
                    $(this.getSelector('submit')).trigger('click');
                } else {
                    this.messageContainer.addErrorMessage({
                        'message': $t('Please enter a valid credit card type number')
                    });
                }
            }

        });
    }
);
