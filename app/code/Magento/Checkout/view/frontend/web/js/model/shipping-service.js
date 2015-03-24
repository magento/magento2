/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(["ko", "jquery", 'mage/storage', 'Magento_Checkout/js/model/quote', 'mage/url'], function(ko, $, storage, quote, urlBuilder) {
    var rates = ko.observableArray([]);
    var shippingCodePrice = ko.observable('');
    var selectedShippingMethod =  ko.observableArray(false);

    quote.getShippingAddress().subscribe(function () {
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: urlBuilder.build('rest/default/V1/carts/' + quote.getQuoteId() + '/shipping-methods'),
            async: false,
            success: function(data) {
                var ratesData = [];
                var prices = [];
                rates.removeAll();
                $.each(data, function(key, entity){
                    if(!ratesData.hasOwnProperty(entity.carrier_code)){
                        ratesData[entity.carrier_code] = [];
                        ratesData[entity.carrier_code]['items'] = [];
                        ratesData[entity.carrier_code]['carrier_code'] = [];
                        ratesData[entity.carrier_code]['carrier_code'] = entity.carrier_code;
                        ratesData[entity.carrier_code]['carrier_title'] = [];
                        ratesData[entity.carrier_code]['carrier_title'] = entity.carrier_title;
                    }
                    ratesData[entity.carrier_code]['items'].push(entity);
                    prices.push('"' + entity.method_code + '":' + entity.amount);

                });
                for (var i in ratesData){
                    rates.push(ratesData[i]);
                }
                shippingCodePrice(prices.toString());
            },
            error: function(data) {
                rates([]);
            }
        });
    });
    return {
        getRates: function() {
            return rates;
        },
        getShippingPrices: function() {

            return shippingCodePrice;
        },

        getSelectedShippingMethod: function(quote) {
            var selectedShipmentMethod = [];
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: urlBuilder.build('rest/default/V1/carts/' + quote.getQuoteId() + '/selected-shipping-method'),
                async: false,
                success: function(data) {
                    selectedShipmentMethod = data;
                },
                error: function(data) {
                    selectedShipmentMethod = [];
                }
            });
            if (selectedShipmentMethod.hasOwnProperty('method_code')) {
                selectedShippingMethod(selectedShipmentMethod.method_code);
            }
            return selectedShippingMethod;
        }

    }
});
