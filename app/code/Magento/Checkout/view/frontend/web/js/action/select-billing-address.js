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
        return function(billingAddressId, useForShipping, formKey) {
            if (!billingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }
            return quote.setBillingAddress(addressList.getAddressById(billingAddressId), useForShipping);
        }
    }
);
