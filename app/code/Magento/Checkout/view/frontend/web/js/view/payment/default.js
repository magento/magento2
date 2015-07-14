/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (ko, $, Component, placeOrderAction, selectPaymentMethodAction, quote, customer, paymentService) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: true,
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),
            /**
             * Initialize view.
             *
             * @returns {Component} Chainable.
             */
            initialize: function () {
                this._super().initChildren();

                quote.billingAddress.subscribe(function(address) {
                    this.isPlaceOrderActionAllowed((address !== null));
                }, this);

                return this;
            },

            /**
             * Initialize child elements
             *
             * @returns {Component} Chainable.
             */
            initChildren: function () {
                return this;
            },

            /**
             * Place order.
             */
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), this.redirectAfterPlaceOrder);

                    $.when(placeOrder).fail(function(){
                        self.isPlaceOrderActionAllowed(true);
                    });
                    return true;
                }
                return false;
            },

            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
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
            getData: function() {
                return {
                    "method": this.item.method,
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
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

            validate: function () {
                return true;
            },

            getBillingAddressFormName: function() {
                return 'billing-address-form-' + this.item.method;
            },

            disposeSubscriptions: function () {
                // dispose all active subscriptions
            }
        });
    }
);
