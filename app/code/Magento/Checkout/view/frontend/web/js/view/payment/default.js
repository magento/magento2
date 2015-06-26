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
        'Magento_Customer/js/model/customer'
    ],
    function (ko, $, Component, placeOrderAction, selectPaymentMethodAction, quote, customer) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: true,
            /**
             * Initialize view.
             *
             * @returns {Component} Chainable.
             */
            initialize: function () {
                this._super().initChildren();
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
            placeOrder: function () {
                var emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate()) {
                    placeOrderAction(this.getData(), this.redirectAfterPlaceOrder);
                }
            },

            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                return true;
            },

            isEnabled: ko.computed(function () {
                return quote.paymentMethod()
                        ? quote.paymentMethod().method
                        : null;
                }
            ),

            isChecked: ko.computed(function () {
                    return quote.paymentMethod()
                        ? quote.paymentMethod().method
                        : null;
                }
            ),

            /**
             * Get payment method data
             */
            getData: function() {
                return {
                    "method": this.item.code,
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
                return this.item.code;
            },

            validate: function () {
                return true;
            },

            getBillingAddressFormName: function() {
                return 'billing-address-form-' + this.item.code;
            },

            disposeSubscriptions: function () {
                // dispose all active subscriptions
            }
        });
    }
);
