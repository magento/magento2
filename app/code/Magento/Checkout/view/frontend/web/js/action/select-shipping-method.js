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
        '../model/url-builder',
        '../model/step-navigator',
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function (quote, urlBuilder, navigator, storage, errorList) {
        return function (code) {
            if (!code) {
                alert('Please specify a shipping method');
            }
            var shippingMethodCode = code.split("_");
            var shippingMethodData ={
                "cartId": quote.getQuoteId(),
                "carrierCode" : shippingMethodCode[0],
                "methodCode" : shippingMethodCode[1]

            };
            return storage.put(
                urlBuilder.createUrl('/carts/:quoteId/selected-shipping-method', {quoteId: quote.getQuoteId()}),
                JSON.stringify(shippingMethodData)
            ).done(
                function(response) {
                    quote.setShippingMethod(shippingMethodCode);
                    quote.setSelectedShippingMethod(code);
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
