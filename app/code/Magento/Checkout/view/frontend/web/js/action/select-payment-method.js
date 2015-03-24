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
        'Magento_Ui/js/model/errorlist',
        'mage/storage',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(quote, errorList, storage, navigator) {
        return function (paymentMethodCode, additionalData) {
            // TODO add support of additional payment data for more complex payments
            var paymentMethodData = {
                "cartId": quote.getQuoteId(),
                "method": {
                    "method": paymentMethodCode,
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
                }
            };
            return storage.put(
                '/rest/default/V1/carts/' + quote.getQuoteId() + '/selected-payment-methods',
                JSON.stringify(paymentMethodData)
            ).done(
                function() {
                    quote.setPaymentMethod(paymentMethodCode);
                    navigator.setCurrent('paymentMethod').goNext();
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setPaymentMethod(null);
                }
            )
        }
    }
);
