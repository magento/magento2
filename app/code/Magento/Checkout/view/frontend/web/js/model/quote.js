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
        'mage/storage'
    ],
    function($, ko, storage) {
        var billingAddress,
            shippingAddress,
            shippingMethod,
            paymentMethod,
            quoteData;
        var quoteHasShippingAddress = ko.observable(false);
        quoteData = window.cartData;

        return {
            getQuoteId: function() {
                return quoteData.entity_id;
            },
            setBillingAddress: function (billingAddress) {
                return storage.post(
                    '/rest/default/V1/carts/' + this.getQuoteId()  + '/billing-address',
                    JSON.stringify(
                        {
                            "cartId": this.getQuoteId(),
                            "address": billingAddress
                        }
                    )
                ).done(
                    function (response) {
                        console.log('Billing address set. Id: ' + response);
                    }
                );
            },
            getBillingAddress: function() {
                return billingAddress;
            },
            setShippingAddress: function (address) {
                shippingAddress = address;
                quoteHasShippingAddress((shippingAddress != null));
            },
            hasShippingAddress: function() {
                return quoteHasShippingAddress;
            },
            getShippingAddress: function() {
                return shippingAddress;
            },
            setShippingMethod: function(billingAddressId, shipToSame) {
                return storage.post(
                    '/checkout/onepage/saveBilling',
                    {'billing_address_id': billingAddressId, 'billing': {'use_for_shipping': shipToSame}}
                ).done(
                    function() {
                        billingAddress = billingAddressId;
                    }
                );
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
                ).done(function() {
                    paymentMethod = paymentMethodCode;
                });
            },
            getPaymentMethod: function() {
                return paymentMethod;
            }
        };
    }
);
