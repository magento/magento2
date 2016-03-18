/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/quote',
        'braintree',
        'underscore',
        'jquery',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'uiRegistry',
        'mage/utils/wrapper'
    ],
    function (
        ko,
        Component,
        setPaymentInformationAction,
        quote,
        braintreeClientSDK,
        _,
        $,
        messageList,
        $t
    ) {
        'use strict';

        var configBraintree = window.checkoutConfig.payment.braintree;

        return Component.extend({
            placeOrderHandler: null,
            validateHandler: null,

            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {*}
             */
            getSource: function () {
                return window.checkoutConfig.payment.iframe.source[this.getCode()];
            },

            /**
             * @returns {*}
             */
            getControllerName: function () {
                return window.checkoutConfig.payment.iframe.controllerName[this.getCode()];
            },

            /**
             * @returns {*}
             */
            getPlaceOrderUrl: function () {
                return window.checkoutConfig.payment.iframe.placeOrderUrl[this.getCode()];
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },

            defaults: {
                template: 'Magento_Braintree/payment/cc-form',
                isCcFormShown: true,
                storeInVault: true,
                paymentMethodNonce: null,
                selectedCardToken: configBraintree ? configBraintree.selectedCardToken : '',
                storedCards: configBraintree ? configBraintree.storedCards : {},
                availableCardTypes: configBraintree ? configBraintree.availableCardTypes : {},
                lastBillingAddress: null
            },

            /**
             * @function
             */
            initVars: function () {
                this.ajaxGenerateNonceUrl = configBraintree ? configBraintree.ajaxGenerateNonceUrl : '';
                this.clientToken = configBraintree ? configBraintree.clientToken : '';
                this.braintreeDataJs = configBraintree ? configBraintree.braintreeDataJs : '';
                this.canSaveCard = configBraintree ? configBraintree.canSaveCard : false;
                this.show3dSecure = configBraintree ? configBraintree.show3dSecure : false;
                this.isFraudDetectionEnabled = configBraintree ? configBraintree.isFraudDetectionEnabled : false;
                this.deviceData = '';
                this.deviceDataElementId = '#device_data';
                this.braintreeDataFrameLoaded = false;
                this.isBound = false;
                this.ccToken = '';
                this.isPaymentProcessing = null;
                this.braintreeClient = null;
                this.quoteBaseGrandTotals = quote.totals()['base_grand_total'];
            },

            /**
             * @returns {*|String}
             */
            canInitialise: function () {
                return this.clientToken;
            },

            /**
             * @override
             */
            initObservable: function () {
                var self = this;

                this.initVars();
                this._super()
                    .track('availableCcValues')
                    .observe([
                        'selectedCardToken',
                        'storeInVault',
                        'storedCards',
                        'paymentMethodNonce',
                        'verified'
                    ]);
                this.isCcFormShown = ko.computed(function () {

                    return !this.useVault() ||
                        this.selectedCardToken() === undefined ||
                        this.selectedCardToken() === '';
                }, this);

                if (!this.braintreeDataFrameLoaded && this.isFraudDetectionEnabled) {
                    $.getScript(this.braintreeDataJs, function () {
                        self.braintreeDataFrameLoaded = true;
                    });
                }

                if (this.canInitialise()) {
                    this.braintreeClient = new braintreeClientSDK.api.Client({
                        clientToken: this.clientToken
                    });
                } else {
                    this.messageContainer.addErrorMessage({
                        'message': $t('Can not initialize PayPal (Braintree)')
                    });
                }

                // subscribe on billing address update
                quote.billingAddress.subscribe(function () {
                    self.updateAvailableTypeValues();
                });

                return this;
            },

            /**
             * Prepare and process payment information
             */
            preparePayment: function () {
                var self = this,
                    cardInfo = null;

                if (this.validateHandler()) {
                    this.messageContainer.clear();
                    this.quoteBaseGrandTotals = quote.totals()['base_grand_total'];

                    this.isPaymentProcessing = $.Deferred();
                    $.when(this.isPaymentProcessing).done(
                        function () {
                            self.placeOrder();
                        }
                    ).fail(
                        function (result) {
                            self.handleError(result);
                        }
                    );

                    this.getFraudAdditionalData();

                    if (this.show3dSecure && this.selectedCardToken()) {
                        this.verify3DSWithToken();

                        return;
                    }

                    if (this.selectedCardToken()) {
                        this.isPaymentProcessing.resolve();

                        return;
                    }

                    cardInfo = {
                        number: this.creditCardNumber(),
                        expirationMonth: this.creditCardExpMonth(),
                        expirationYear: this.creditCardExpYear(),
                        cvv: this.creditCardVerificationNumber()
                    };
                    this.braintreeClient.tokenizeCard(cardInfo, function (error, nonce) {
                        if (error) {
                            self.isPaymentProcessing.reject(error);

                            return;
                        }

                        self.paymentMethodNonce(nonce);

                        if (self.show3dSecure) {
                            self.verify3DS();

                            return;
                        }

                        self.isPaymentProcessing.resolve();
                    });
                }
            },

            /**
             * @override
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_last4': this.creditCardNumber().slice(-4),
                        'store_in_vault': this.storeInVault(),
                        'payment_method_nonce': this.paymentMethodNonce(),
                        'cc_token': this.selectedCardToken(),
                        'device_data': this.deviceData,
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth()
                    }
                };
            },

            /**
             * Display error message on the top of the page and reset payment method nonce.
             * @param {*} error - error message
             */
            handleError: function (error) {
                this.paymentMethodNonce('');

                if (_.isObject(error)) {
                    this.messageContainer.addErrorMessage(error);
                } else {
                    this.messageContainer.addErrorMessage({
                        message: error
                    });
                }
            },

            /**
             * Get payment method nonce from server and perform 3DSecure card verification via braintree client.
             */
            verify3DSWithToken: function () {
                var self = this;

                //Make an ajax call to convert token to payment method nonce and use the nonce for 3dsecure verification
                $.ajax({
                    type: 'POST',
                    url: self.ajaxGenerateNonceUrl,
                    data: {
                        token: this.selectedCardToken()
                    },

                    /**
                     * Success callback for payment method nonce request.
                     * @param {Object} response
                     */
                    success: function (response) {
                        if (response.success) {
                            self.paymentMethodNonce(response.nonce);
                            self.verify3DS();
                        } else {
                            self.isPaymentProcessing.reject(response['error_message']);
                        }
                    },

                    /**
                     * Error callback for payment method nonce request.
                     * @param {*} response
                     */
                    error: function (response) {
                        self.isPaymentProcessing.reject(response);
                    }
                });
            },

            /**
             * 3DSecure card verification via braintree client.
             */
            verify3DS: function () {
                var self = this;

                this.bind3dsecureIframe();
                this.braintreeClient.verify3DS({
                    amount: this.quoteBaseGrandTotals,
                    creditCard: this.paymentMethodNonce()
                }, function (error, response) {
                    var liability = null;

                    if (error) {
                        self.isPaymentProcessing.reject(error);

                        return;
                    }
                    liability = {
                        shifted: response.verificationDetails.liabilityShifted,
                        shiftPossible: response.verificationDetails.liabilityShiftPossible
                    };

                    if (liability.shifted || !liability.shifted && !liability.shiftPossible) {
                        self.paymentMethodNonce(response.nonce);
                        self.isPaymentProcessing.resolve();
                    } else {
                        self.paymentMethodNonce('');
                        self.isPaymentProcessing.reject($t('Please try again with another form of payment.'));
                    }
                });
            },

            /**
             * @override
             */
            getCode: function () {
                return 'braintree';
            },

            /**
             * @returns {*}
             */
            useVault: function () {
                return configBraintree ?
                    configBraintree.useVault :
                    false;
            },

            /**
             * @returns {*}
             */
            isCcDetectionEnabled: function () {
                return configBraintree ?
                    configBraintree.isCcDetectionEnabled :
                    false;
            },

            /**
             * @returns {Array}
             */
            getStoredCards: function () {
                var availableTypes = this.getCcAvailableTypes(),
                    storedCards = this.storedCards(),
                    filteredCards = [],
                    i,
                    storedCardType;

                for (i = 0; i < storedCards.length; i++) {
                    storedCardType = storedCards[i].type;

                    if (typeof availableTypes[storedCardType] != 'undefined') {
                        filteredCards.push(storedCards[i]);
                    }
                }

                return filteredCards;
            },

            /**
             * Get list of available CC types
             */
            getCcAvailableTypes: function () {
                var availableTypes = configBraintree.availableCardTypes,
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId &&
                    typeof configBraintree.countrySpecificCardTypes[billingCountryId] !== 'undefined'
                ) {

                    return this.collectTypes(
                        availableTypes,
                        configBraintree.countrySpecificCardTypes[billingCountryId]
                    );
                }

                return availableTypes;
            },

            /**
             * @param {Object} availableTypes
             * @param {Object} countrySpecificCardTypes
             * @returns {Object}
             */
            collectTypes: function (availableTypes, countrySpecificCardTypes) {
                var key,
                    filteredTypes = {};

                for (key in availableTypes) {
                    if (_.indexOf(countrySpecificCardTypes, key) !== -1) {
                        filteredTypes[key] = availableTypes[key];
                    }
                }

                return filteredTypes;
            },

            /**
             * @returns {exports.context}
             */
            context: function () {
                return this;
            },

            /**
             * Get fraud control token.
             */
            getFraudAdditionalData: function () {
                if ($(this.deviceDataElementId).length > 0 && this.isFraudDetectionEnabled) {
                    this.deviceData = $(this.deviceDataElementId).val();
                }
            },

            /**
             * Fix the non-observed close button on Braintree iframe
             */
            bind3dsecureIframe: function () {
                var self = this,
                    $body = $('body');

                if (!self.isBound) {
                    $body.bind('DOMNodeInserted', function (e) {
                        if (e.target.nodeName === 'IFRAME') {
                            self.isBound = true;
                            $('body').trigger('processStart');
                        }
                    });
                    $body.bind('DOMNodeRemoved', function (e) {
                        if (e.target.nodeName === 'IFRAME') {
                            self.isBound = false;
                            $('body').trigger('processStop');
                        }
                    });
                }
            },

            /**
             * @returns {String}
             */
            getCssClass: function () {
                return this.isCcDetectionEnabled() ? 'field type detection' : 'field type required';
            },

            /**
             * Update list of available CC types values
             */
            updateAvailableTypeValues: function () {
                this.availableCcValues = this.getCcAvailableTypesValues();
            }
        });
    }
);
