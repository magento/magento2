/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mage/storage'], function(storage) {
    var billingAddress, shippingMethod;
    return {
        setBillingAddress: function (billingAddressId, shipToSame) {
            return storage.post(
                'checkout/onepage/saveBilling',
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
        setShippingMethod: function() {
            return storage.post(
                'checkout/onepage/saveBilling',
                {'billing_address_id': billingAddressId, 'billing': {'use_for_shipping': shipToSame}}
            ).done(
                function() {
                    billingAddress = billingAddressId;
                }
            );
        }
    };
});
