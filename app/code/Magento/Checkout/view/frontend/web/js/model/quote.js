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
            return window.cartData.entity_id;
        },
        setData: function(cartData) {
            quoteData = cartData;
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
            return storage.post(
                'rest/default/V1/carts/' + this.getQuoteId() + '/shipping-address',
                JSON.stringify({address: address})
            ).done(
                function(quoteAddressId) {
                    address.id = quoteAddressId;
                    shippingAddress = address;
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
