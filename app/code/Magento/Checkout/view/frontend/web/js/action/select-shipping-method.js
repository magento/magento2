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
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (quote, storage, errorList, navigator) {
        return function (shippingMethodCode) {
            var shippingMethodData ={
                "cartId": quote.getQuoteId(),
                "code" : shippingMethodCode
            };
            return storage.put(
                'rest/V1/carts/' + quote.getQuoteId() + '/selected-shipping-method',
                JSON.stringify(shippingMethodData)
            ).done(
                function() {
                    quote.setShippingMethod(shippingMethodCode);
                    navigator.setCurrent('shippingMethod').goNext();
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setShippingMethod(null);
                }
            );
        }
    }
);
