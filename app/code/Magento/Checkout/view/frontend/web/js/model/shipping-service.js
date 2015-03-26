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
        'jquery',
        'mage/storage',
        '../model/quote',
        '../model/url-builder'
    ],
    function (ko, $, storage, quote, urlBuilder) {
        var rates = ko.observableArray([]);
        quote.getShippingAddress().subscribe(function () {
            storage.get(
                urlBuilder.createUrl('/carts/:quoteId/shipping-methods', {quoteId: quote.getQuoteId()})
            ).success(
                function (data) {
                    quote.setRates(data);
                    var ratesData = [];
                    rates.removeAll();
                    $.each(data, function (key, entity) {
                        if (!ratesData.hasOwnProperty(entity.carrier_code)) {
                            ratesData[entity.carrier_code] = [];
                            ratesData[entity.carrier_code]['items'] = [];
                            ratesData[entity.carrier_code]['carrier_code'] = entity.carrier_code;
                            ratesData[entity.carrier_code]['carrier_title'] = entity.carrier_title;
                        }
                        ratesData[entity.carrier_code]['items'].push(entity);

                    });
                    for (var i in ratesData) {
                        rates.push(ratesData[i]);
                    }
                }
            ).error(
                function (data) {
                    rates([]);
                }
            )

        });
        return {
            getRates: function () {
                return rates;
            }
        }
    }
);
