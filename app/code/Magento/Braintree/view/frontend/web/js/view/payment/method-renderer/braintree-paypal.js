/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'braintree',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function ($, Component, quote, braintreeClientSDK, messageList, $t) {
        var braintreeConfig = window.checkoutConfig.payment.braintree_paypal;

        return Component.extend({
            defaults: {
                template: 'Magento_Braintree/payment/braintree-paypal-form',
                locale: braintreeConfig.locale,
                merchantName: braintreeConfig.merchantDisplayName,
                clientToken: braintreeConfig.clientToken,
                paymentMethodNonce: null,
                containerElement: null,
                totalSubscription: null,
                currentGrandTotal: null
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'paymentMethodNonce'
                    ]);

                this.totalSubscription = quote.totals.subscribe(function () {
                    if (this.currentGrandTotal != quote.totals().base_grand_total) {
                        this.initPayPalContainer();
                    }
                }, this);

                return this;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce()
                    }
                };
            },

            disposeSubscriptions: function () {
                if (this.totalSubscription) {
                    this.totalSubscription.dispose();
                }
            },

            canInitialise: function () {
                return this.clientToken
            },

            initPayPalContainer: function (element) {
                if (this.canInitialise()) {
                    if (element) {
                        // target container element is passed via afterRender data-bind
                        this.containerElement = element;
                    }

                    var container = $(this.containerElement);
                    if (container.length == 0) {
                        return;
                    }

                    var totals = quote.totals();
                    this.paymentMethodNonce(null);
                    // the following line is an optimization to prevent frequent re-initialization of the container
                    this.currentGrandTotal = totals.base_grand_total;
                    container.empty();

                    var self = this;
                    //TODO: check shipping address override
                    braintreeClientSDK.setup(this.clientToken, 'paypal', {
                        container: container,
                        singleUse: true,
                        amount: totals.base_grand_total,
                        currency: totals.base_currency_code,
                        displayName: this.merchantName || '',
                        locale: this.locale,
                        onPaymentMethodReceived: function (response) {
                            self.paymentMethodNonce(response.nonce);
                        },
                        onCancelled: function () {
                            self.paymentMethodNonce(null);
                        }
                    });
                } else {
                    this.messageContainer.addErrorMessage({'message': $t('Can not initialize PayPal (Braintree)')});
                }
            },
            isValid: function () {
                return this.paymentMethodNonce() ? true : false;
            }
        });
    }
);
