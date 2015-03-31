/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko', 'jquery'],
    function (ko, $) {
        var rates = ko.observable([]);
        return {
            shippingRates: ko.observableArray([]),
            getRates: function() {
                return rates;
            },
            setRates: function(ratesData) {
                rates(ratesData);
            },
            setShippingRates: function(ratesData) {
                var self = this;
                this.setRates(ratesData);
                $.each(ratesData, function (key, entity) {
                    var rateEntity = [];
                    rateEntity['items'] = [];
                    if (!ratesData.hasOwnProperty(entity.carrier_code)) {
                        rateEntity['carrier_code'] = entity.carrier_code;
                        rateEntity['carrier_title'] = entity.carrier_title;
                    }
                    rateEntity['items'].push(entity);
                    self.shippingRates.push(rateEntity);
                });

            },
            getSippingRates: function() {
                return this.shippingRates;
            },
            getTitleByCode: function(code) {
                var shippingMethodTitle = '';
                if (code) {
                    $.each(rates(), function (key, entity) {
                        if (entity['carrier_code'] == code[0]
                            && entity['method_code'] == code[1]) {
                            shippingMethodTitle = "(" + entity['carrier_title'] + " - " + entity['method_title'] + ")";
                        }
                    });
                }
                return shippingMethodTitle;
            }
        }
    }
);
