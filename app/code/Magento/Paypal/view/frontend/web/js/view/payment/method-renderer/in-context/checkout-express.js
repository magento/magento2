/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'underscore',
        'jquery',
        'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/lib/view/utils/dom-observer',
        'paypalInContextExpressCheckout',
        'Magento_Customer/js/customer-data'
    ],
    function (
        _,
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        domObserver,
        paypalExpressCheckout,
        customerData
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
                            setPaymentMethodAction(this.messageContainer).done(
                                function () {
                                    $('body').trigger('processStart');

                                    $.get(
                                        this.path,
                                        {
                                            button: 0
                                        }
                                    ).done(
                                        function (response) {
                                            if (response && response.url) {
                                                paypalExpressCheckout.checkout.startFlow(response.url);

                                                return;
                                            }

                                            paypalExpressCheckout.checkout.closeFlow();
                                            window.location.reload();
                                        }
                                    ).fail(
                                        function () {
                                            paypalExpressCheckout.checkout.closeFlow();
                                            window.location.reload();
                                        }
                                    ).always(
                                        function () {
                                            $('body').trigger('processStop');
                                            customerData.invalidate(['cart']);
                                        }
                                    );

                                }.bind(this)
                            );
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

            /**
             * @returns {Object}
             */
            initClient: function () {
                var selector = '#' + this.getButtonId();

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);

                if (!clientInit) {
                    domObserver.get(selector, function () {
                        paypalExpressCheckout.checkout.setup(this.merchantId, this.clientConfig);
                        clientInit = true;
                        domObserver.off(selector);
                    }.bind(this));
                } else {
                    domObserver.get(selector, function () {
                        $(selector).on('click', this.clientConfig.click);
                        domObserver.off(selector);
                    }.bind(this));
                }

                return this;
            },

            /**
             * @returns {String}
             */
            getButtonId: function () {
                return this.inContextId;
            }
        });
    }
);
