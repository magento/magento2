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
        'ko',
        '../model/quote',
        'mage/storage'
    ],

    function (ko, quote, storage) {
        var availablePaymentMethods = ko.observableArray([]);
        quote.getBillingAddress().subscribe(function () {
            storage.get('rest/default/V1/carts/' + quote.getQuoteId() + '/payment-methods').
                success(function (data) {
                    availablePaymentMethods(data);
                }).
                error(function (data) {
                    availablePaymentMethods([]);
                }
            )

        });
        return {
            getAvailablePaymentMethods: function () {
                return availablePaymentMethods;
            }
        }
    }
);
