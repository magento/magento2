/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mage/storage'], function(storage) {
    var billingAddress,
        shippingAddress,
        shippingMethod,
        quoteData;
    return {
        getQuoteId: function() {
            return quoteData.entity_id;
        },
        setData: function(cartData) {
            quoteData = cartData;
        },
        setBillingAddress: function (billingAddressId, shipToSame) {
            return storage.post(
                '/checkout/onepage/saveBilling',
                {'billing_address_id': billingAddressId, 'billing': {'use_for_shipping': shipToSame}}
            ).done(
                function() {
                    billingAddress = billingAddressId;
                }
            );
        },
        getBillingAddress: function() {
            return billingAddress;
        },
        setShippingAddress: function (shippingAddressId) {
            return storage.post(
                '/checkout/onepage/saveShipping',
                {'shipping_address_id': shippingAddressId}
            ).done(
                function() {
                    shippingAddress = shippingAddressId;
                }
            );
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
        }
    };
});
