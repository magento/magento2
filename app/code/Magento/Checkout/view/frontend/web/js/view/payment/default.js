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
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'uiRegistry'
    ],
    function (
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
        registry
    ) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: true,
            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                //
            },
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
                checkoutDataResolver.resolveBillingAddress();

                var billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var defaultAddressData = checkoutProvider.get(billingAddressCode);
                    if (defaultAddressData === undefined) {
                        // skip if payment does not have a billing address form
                        return;
                    }
                    var billingAddressData = checkoutData.getBillingAddressFromData();
                    if (billingAddressData) {
                        checkoutProvider.set(
                            billingAddressCode,
                            $.extend({}, defaultAddressData, billingAddressData)
                        );
                    }
                    checkoutProvider.on(billingAddressCode, function (billingAddressData) {
                        checkoutData.setBillingAddressFromData(billingAddressData);
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
                    }).done(this.afterPlaceOrder);
                    return true;
                }
                return false;
            },

            selectPaymentMethod: function() {
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
                var billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.off(billingAddressCode);
                });
            }
        });
    }
);
