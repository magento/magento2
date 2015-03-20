/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['../model/quote', '../model/addresslist', 'mage/storage', 'select-shipping-address'],
    function(quote, addressList, storage, shippingAddressAction) {
        return function(billingAddressId, useForShipping, formKey) {
            var billingAddress = addressList.getAddressById(billingAddressId);
            if (!billingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }
            storage.post(
                '/rest/default/V1/carts/' + quote.getQuoteId()  + '/billing-address',
                JSON.stringify(
                    {
                        "cartId": quote.getQuoteId(),
                        "address": billingAddress
                    }
                )
            ).success(
                function (response) {
                    billingAddress.id = response;
                    quote.setBillingAddress(billingAddress);
                    if (useForShipping === '1') {
                        //TODO: Find a better way to set shipping address.
                        quote.setShippingAddress(billingAddressId);
                    }
                }
            ).error(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setBillingAddress(null);
                }
            );
        }
    }
);
