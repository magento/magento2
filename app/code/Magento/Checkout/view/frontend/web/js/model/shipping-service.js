/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko', 'jquery', '../model/quote'],
    function (ko, $, quote) {
        return {
            shippingRates: ko.observableArray([]),
            setShippingRates: function(ratesData) {
                var self = this;
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
                quote.setRates(this.shippingRates());
            },
            getRates: function() {
                return this.shippingRates;
            }
        }
    }
);
