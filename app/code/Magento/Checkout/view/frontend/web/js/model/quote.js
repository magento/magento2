/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function($, ko, storage, navigator) {
        var billingAddress,
            shippingAddress,
            shippingMethod,
            paymentMethod;

        var quoteHasBillingAddress = ko.observable(false);
        var quoteHasShippingAddress = ko.observable(false);
        var quoteHasShippingMethod = ko.observable(false);
        var quoteHasPaymentMethod = ko.observable(false);
        var quoteData = window.cartData;
        var currencySymbol = window.currencySymbol;
        return {
            getQuoteId: function() {
                return quoteData.entity_id;
            },
            getCurrencySymbol: function() {
              return currencySymbol.data;
            },
            setBillingAddress: function (address) {
                billingAddress = address;
                quoteHasBillingAddress(billingAddress !== null);
                navigator.setStepVisible('billingAddress', false);
                navigator.setStepVisible('shippingAddress', true);
            },
            getBillingAddress: function() {
                return billingAddress;
            },
            hasBillingAddress: function() {
                return quoteHasBillingAddress;
            },
            setShippingAddress: function (address) {
                shippingAddress = address;
                quoteHasShippingAddress((shippingAddress != null));
                navigator.setStepVisible('shippingAddress', false);
                navigator.setStepVisible('shippingMethod', true);
            },
            hasShippingAddress: function() {
                return quoteHasShippingAddress;
            },
            getShippingAddress: function() {
                return shippingAddress;
            },
            setPaymentMethod: function(paymentMethodCode, additionalData) {
                // TODO add support of additional payment data for more complex payments
                var paymentMethodData = {
                    "cartId": this.getQuoteId(),
                    "method": {
                        "method": paymentMethodCode,
                        "po_number": null,
                        "cc_owner": null,
                        "cc_number": null,
                        "cc_type": null,
                        "cc_exp_year": null,
                        "cc_exp_month": null,
                        "additional_data": null
                    }
                };
                return storage.put(
                    '/rest/default/V1/carts/' + this.getQuoteId() + '/selected-payment-methods',
                    JSON.stringify(paymentMethodData)
                ).done(
                    function() {
                        paymentMethod = paymentMethodCode;
                        quoteHasPaymentMethod((paymentMethod != null));
                        navigator.setStepVisible('paymentMethod', false);
                        navigator.setStepVisible('review', true);

                    }
                );
            },
            getPaymentMethod: function() {
                return paymentMethod;
            },
            hasPaymentMethod: function() {
                return quoteHasPaymentMethod;
            },
            setShippingMethod: function(shippingMethodCode) {
                var shippingMethodData ={
                    "cartId": this.getQuoteId(),
                    "code" : shippingMethodCode
                };
                return storage.put(
                    'rest/V1/carts/' + this.getQuoteId() + '/selected-shipping-method',
                    JSON.stringify(shippingMethodData)
                ).done(
                    function() {
                        shippingMethod = shippingMethodCode;
                        quoteHasShippingMethod((shippingMethod != null));
                        navigator.setStepVisible('shippingMethod', false);
                        navigator.setStepVisible('paymentMethod', true);
                    }
                );
            },
            getShippingMethod: function() {
                return shippingMethod;
            },
            hasShippingMethod: function() {
                return quoteHasShippingMethod;
            }
        };
    }
);
