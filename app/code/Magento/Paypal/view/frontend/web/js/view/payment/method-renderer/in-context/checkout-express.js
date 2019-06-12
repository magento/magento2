/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
define(
    [
        'underscore',
        'jquery',
        'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/lib/view/utils/dom-observer',
        'paypalInContextExpressCheckout',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        _,
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        domObserver,
        paypalExpressCheckout,
        customerData,
        messageList
    ) {
        'use strict';

        // State of PayPal module initialization
        var clientInit = false;

        return Component.extend({

            defaults: {
                template: 'Magento_Paypal/payment/paypal-express-in-context',
                clientConfig: {
                    /**
                     * @param {Object} event
                     */
                    click: function (event) {
                        event.preventDefault();

                        if (additionalValidators.validate()) {
                            paypalExpressCheckout.checkout.initXO();

                            this.selectPaymentMethod();
                            setPaymentMethodAction(this.messageContainer).done(function () {
                                $('body').trigger('processStart');

                                $.getJSON(this.path, {
                                    button: 0
                                }).done(function (response) {
                                    var message = response && response.message;

                                    if (message) {
                                        if (message.type === 'error') {
                                            messageList.addErrorMessage({
                                                message: message.text
                                            });
                                        } else {
                                            messageList.addSuccessMessage({
                                                message: message.text
                                            });
                                        }
                                    }

                                    if (response && response.url) {
                                        paypalExpressCheckout.checkout.startFlow(response.url);

                                        return;
                                    }

                                    paypalExpressCheckout.checkout.closeFlow();
                                }).fail(function () {
                                    paypalExpressCheckout.checkout.closeFlow();
                                }).always(function () {
                                    $('body').trigger('processStop');
                                    customerData.invalidate(['cart']);
                                });
                            }.bind(this)).fail(function () {
                                paypalExpressCheckout.checkout.closeFlow();
                            });
                        }
                    }
                }
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();
                this.initClient();

                return this;
            },
=======
define([
    'jquery',
    'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
    'Magento_Paypal/js/in-context/express-checkout-wrapper',
    'Magento_Paypal/js/action/set-payment-method',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messageList',
    'Magento_Ui/js/lib/view/utils/async'
], function ($, Component, Wrapper, setPaymentMethod, additionalValidators, messageList) {
    'use strict';

    return Component.extend(Wrapper).extend({
        defaults: {
            template: 'Magento_Paypal/payment/paypal-express-in-context',
            validationElements: 'input'
        },

        /**
         * Listens element on change and validate it.
         *
         * @param {HTMLElement} context
         */
        initListeners: function (context) {
            $.async(this.validationElements, context, function (element) {
                $(element).on('change', function () {
                    this.validate();
                }.bind(this));
            }.bind(this));
        },

        /**
         *  Validates Smart Buttons
         */
        validate: function () {
            this._super();

            if (this.actions) {
                additionalValidators.validate(true) ? this.actions.enable() : this.actions.disable();
            }
        },
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        /** @inheritdoc */
        beforePayment: function (resolve, reject) {
            var promise = $.Deferred();

            setPaymentMethod(this.messageContainer).done(function () {
                return promise.resolve();
            }).fail(function (response) {
                var error;

                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = this.paymentActionError;
                }

                this.addError(error);

                return reject(new Error(error));
            }.bind(this));

            return promise;
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this._super();
            this.clientConfig.quoteId = window.checkoutConfig.quoteData['entity_id'];
            this.clientConfig.customerId = window.customerData.id;
            this.clientConfig.merchantId = this.merchantId;
            this.clientConfig.button = 0;
            this.clientConfig.commit = true;

            return this.clientConfig;
        },

        /**
         * Adding logic to be triggered onClick action for smart buttons component
         */
        onClick: function () {
            additionalValidators.validate();
            this.selectPaymentMethod();
        },

        /**
         * Adds error message
         *
         * @param {String} message
         */
        addError: function (message) {
            messageList.addErrorMessage({
                message: message
            });
        }
    });
});
