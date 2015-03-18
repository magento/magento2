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
        return function(shippingAddressId, formKey) {
            var address = addressList.getAddressById(shippingAddressId);
            return quote.setShippingAddress(address);
        }
    }
);
