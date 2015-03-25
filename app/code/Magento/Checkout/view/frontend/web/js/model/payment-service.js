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
        '../model/url-builder',
        'mage/storage'
    ],

    function (ko, quote, urlBuilder, storage) {
        var availablePaymentMethods = ko.observableArray([]);
        quote.getBillingAddress().subscribe(function () {
            storage.get(
                urlBuilder.createUrl('/carts/:quoteId/payment-methods', {quoteId: quote.getQuoteId()})
            ).success(
                function (data) {
                    availablePaymentMethods(data);
                }
            ).error(
                function () {
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
