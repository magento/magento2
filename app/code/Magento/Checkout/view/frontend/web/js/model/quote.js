/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko'],
    function(ko) {
        var billingAddress = ko.observable(null);
        var shippingAddress = ko.observable(null);
        var shippingMethod = ko.observable(null);
        var paymentMethod = ko.observable(null);
        var quoteData = window.cartData;
        var currencySymbol = window.currencySymbol;
        var selectedShippingMethod = window.selectedShippingMethod;
        var storeCode = window.storeCode;
        return {
            getQuoteId: function() {
                return quoteData.entity_id;
            },
            isVirtual: function() {
                return !!Number(quoteData.is_virtual);
            },
            getCurrencySymbol: function() {
              return currencySymbol.data;
            },
            getItems: function() {
                return window.cartItems;
            },
            getTotals: function() {
                return {
                    'subtotal': quoteData.subtotal,
                    'subtotal_with_discount': quoteData.subtotal_with_discount,
                    'grandtotal': quoteData.grandtotal
                }
            },
            setBillingAddress: function (address, useForShipping) {
                billingAddress(address);
            },
            getBillingAddress: function() {
                return billingAddress;
            },
            setShippingAddress: function (address) {
                shippingAddress(address);
            },
            getShippingAddress: function() {
                return shippingAddress;
            },
            setPaymentMethod: function(paymentMethodCode) {
                paymentMethod(paymentMethodCode);
            },
            getPaymentMethod: function() {
                return paymentMethod;
            },
            setShippingMethod: function(shippingMethodCode) {
                shippingMethod(shippingMethodCode);
            },
            getShippingMethod: function() {
                return shippingMethod;
            },
            getSelectedShippingMethod: function() {
                return selectedShippingMethod;
            },
            getStoreCode: function() {
                return storeCode;
            }
        };
    }
);
