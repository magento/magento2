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
        'jquery',
        '../model/quote',
        'mage/url',
    ],
    function($, quote, urlBuilder) {
        return {
            getAvailablePaymentMethods: function(quote) {
                var availablePaymentMethods = [];
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: urlBuilder.build('rest/default/V1/carts/' + quote.getQuoteId() + '/payment-methods'),
                    async: false,
                    success: function(data) {
                        availablePaymentMethods = data;
                    }
                });
                return availablePaymentMethods;
            }
        }
    }
);
