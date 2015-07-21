/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var checkoutDataValue = storage.get('checkout-data')();
    var isObjEmpty = $.isEmptyObject(checkoutDataValue);
    if (isObjEmpty) {
        var checkoutData = {
            'selectedShippingAddress': null,
            'shippingAddressData' : null,
            'selectedShippingRate' : null,
            'selectedPaymentMethod' : null,
            'billingAddressData' : null
        };
        storage.set('checkout-data', checkoutData);
    }

    return {
        getData: function() {
            return storage.get('checkout-data')();
        },

        setSelectedShippingAddress: function (data) {
            var obj = this.getData();
            obj.selectedShippingAddress = data;
            storage.set('checkout-data', checkoutData);
        },

        setShippingAddressData: function (data) {
            var obj = this.getData();
            obj.shippingAddressData = data;
            storage.set('checkout-data', checkoutData);
        },

        setSelectedShippingRate: function (data) {
            var obj = this.getData();
            obj.selectedShippingRate = data;
            storage.set('checkout-data', checkoutData);
        },

        setSelectedPaymentMethod: function (data) {
            var obj = this.getData();
            obj.selectedPaymentMethod = data;
            storage.set('checkout-data', checkoutData);
        },

        setBillingAddressData: function (data) {
            var obj = this.getData();
            obj.billingAddressData = data;
            storage.set('checkout-data', checkoutData);
        }
    }
});
