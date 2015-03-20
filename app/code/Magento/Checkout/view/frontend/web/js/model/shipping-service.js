/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(["jquery", 'mage/storage', '../model/quote', 'mage/url'], function($, storage, quote, urlBuilder) {
    return {
        getAvailableShippingMethods: function(quote) {
            var availableShipmentMethods = [];
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: urlBuilder.build('rest/default/V1/carts/' + quote.getQuoteId() + '/shipping-methods'),
                async: false,
                success: function(data) {
                    availableShipmentMethods = data;
                }
            });
            //sort by carrier_code
            return availableShipmentMethods;
        },

        sortRates: function(data) {
            var rates = [];
            var filteredRates = [];
            $.each(data, function(key, entity){
                if(!rates.hasOwnProperty(entity.carrier_code)){
                    rates[entity.carrier_code] = [];
                    rates[entity.carrier_code]['items'] = [];
                    rates[entity.carrier_code]['carrier_code'] = [];
                    rates[entity.carrier_code]['carrier_code'] = entity.carrier_code;
                    rates[entity.carrier_code]['carrier_title'] = [];
                    rates[entity.carrier_code]['carrier_title'] = entity.carrier_title;
                }
                rates[entity.carrier_code]['items'].push(entity);
            });
            for (var i in rates)
            {
                filteredRates.push(rates[i]);
            }
            return filteredRates;
        },
        getSelectedShippingMethod: function(quote) {
            var selectedShipmentMethod = [];
            var methodCode = null;
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: urlBuilder.build('rest/default/V1/carts/' + quote.getQuoteId() + '/selected-shipping-method'),
                async: false,
                success: function(data) {
                    selectedShipmentMethod = data;
                }
            });
            if (selectedShipmentMethod.hasOwnProperty('method_code')) {
                methodCode = selectedShipmentMethod.method_code;
            }
            return methodCode;
        },
        getShippingCodePrice: function(data) {
            var shippingCodePrice = [];
            $.each(data, function(key, entity){
                var priceString = '"' + entity.method_code + '":' + entity.amount;
                shippingCodePrice.push(priceString);
            });

            return shippingCodePrice.toString();
        }
    }
});
