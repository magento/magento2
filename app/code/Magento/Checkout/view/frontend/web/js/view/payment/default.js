/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'uiRegistry',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messages',
    'uiLayout',
    'Magento_Checkout/js/action/redirect-on-success'
], function (
    ko,
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    quote,
    customer,
    paymentService,
    checkoutData,
    checkoutDataResolver,
    registry,
    additionalValidators,
    Messages,
    layout,
    redirectOnSuccessAction
) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: true,
        isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            // Override this function and put after place order logic here
        },

        /**
         * Initialize view.
         *
         * @return {exports}
         */
        initialize: function () {
            var billingAddressCode,
                billingAddressData,
                defaultAddressData;

            this._super().initChildren();
            quote.billingAddress.subscribe(function (address) {
                this.isPlaceOrderActionAllowed(address !== null);
            }, this);
            checkoutDataResolver.resolveBillingAddress();

            billingAddressCode = 'billingAddress' + this.getCode();
            registry.async('checkoutProvider')(function (checkoutProvider) {
                defaultAddressData = checkoutProvider.get(billingAddressCode);

                if (defaultAddressData === undefined) {
                    // Skip if payment does not have a billing address form
                    return;
                }
                billingAddressData = checkoutData.getBillingAddressFromData();

                if (billingAddressData) {
                    checkoutProvider.set(
                        billingAddressCode,
                        $.extend(true, {}, defaultAddressData, billingAddressData)
                    );
                }
                checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                    checkoutData.setBillingAddressFromData(providerBillingAddressData);
                }, billingAddressCode);
            });

            return this;
        },

        /**
         * Initialize child elements
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            this.messageContainer = new Messages();
            this.createMessagesComponent();

            return this;
        },

        /**
         * Create child message renderer component
         *
         * @returns {Component} Chainable.
         */
        createMessagesComponent: function () {

            var messagesComponent = {
                parent: this.name,
                name: this.name + '.messages',
                displayArea: 'messages',
                component: 'Magento_Ui/js/view/messages',
                config: {
                    messageContainer: this.messageContainer
                }
            };

            layout([messagesComponent]);

            return this;
        },

        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                        function () {
                            self.afterPlaceOrder();

                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    );

                return true;
            }

            return false;
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },

        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            selectPaymentMethodAction(this.getData());
            checkoutData.setSelectedPaymentMethod(this.item.method);

            return true;
        },

        isChecked: ko.computed(function () {
            return quote.paymentMethod() ? quote.paymentMethod().method : null;
        }),

        isRadioButtonVisible: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length !== 1;
        }),

        /**
         * Get payment method data
         */
        getData: function () {
            return {
                'method': this.item.method,
                'po_number': null,
                'additional_data': null
            };
        },

        /**
         * Get payment method type.
         */
        getTitle: function () {
            return this.item.title;
        },

        /**
         * Get payment method code.
         */
        getCode: function () {
            return this.item.method;
        },

        /**
         * @return {Boolean}
         */
        validate: function () {
            return true;
        },

        /**
         * @return {String}
         */
        getBillingAddressFormName: function () {
            return 'billing-address-form-' + this.item.method;
        },

        /**
         * Dispose billing address subscriptions
         */
        disposeSubscriptions: function () {
            // dispose all active subscriptions
            var billingAddressCode = 'billingAddress' + this.getCode();

            registry.async('checkoutProvider')(function (checkoutProvider) {
                checkoutProvider.off(billingAddressCode);
            });
        }
    });
});
