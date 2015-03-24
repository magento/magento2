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
        '../model/quote',
        '../model/addresslist',
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(quote, addressList, storage, errorList, navigator) {
        return function(shippingAddressId, sameAsBilling) {
            if (!shippingAddressId) {
                alert('Currently adding a new address is not supported.');
                return false;
            }
            var address = addressList.getAddressById(shippingAddressId);
            address.sameAsBilling = sameAsBilling;

            storage.post(
                'rest/default/V1/carts/' + quote.getQuoteId() + '/shipping-address',
                JSON.stringify({address: address})
            ).done(
                function(quoteAddressId) {
                    address.id = quoteAddressId;
                    quote.setShippingAddress(address);
                    navigator.setCurrent('shippingAddress').goNext();
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setShippingAddress(null);
                }
            );
        }
    }
);
