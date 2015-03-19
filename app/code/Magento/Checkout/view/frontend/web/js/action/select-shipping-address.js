/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['../model/quote', '../model/addresslist'],
    function(quote, addressList) {
        return function(shippingAddressId, sameAsBilling, formKey) {
            var address = addressList.getAddressById(shippingAddressId);
            address.sameAsBilling = sameAsBilling;
            return quote.setShippingAddress(address);
        }
    }
);
