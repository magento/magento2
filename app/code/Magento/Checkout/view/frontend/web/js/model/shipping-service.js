/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['ko', 'jquery'],
    function (ko, $) {
        "use strict";
        var rates = ko.observable([]);
        return {
            shippingRates: ko.observableArray([]),
            setShippingRates: function(ratesData) {
                var self = this;
                rates(ratesData);
                self.shippingRates([]);
                $.each(ratesData, function (key, entity) {
                    var rateEntity = {};
                    rateEntity.items = [];
                    if (!ratesData.hasOwnProperty(entity.carrier_code)) {
                        rateEntity['carrier_code'] = entity.carrier_code;
                        rateEntity['carrier_title'] = entity.carrier_title;
                    }
                    rateEntity.items.push(entity);
                    self.shippingRates.push(rateEntity);
                });

            },
            getSippingRates: function() {
                return this.shippingRates;
            },
            getTitleByCode: function(methodCodeParts) {
                var shippingMethodTitle = '', shippingMethodCode, carrierCode, methodCode;
                if (!methodCodeParts) {
                    return shippingMethodTitle;
                }
                shippingMethodCode = methodCodeParts.slice(0);
                carrierCode = shippingMethodCode.shift();
                methodCode = shippingMethodCode.join('_');
                $.each(rates(), function (key, entity) {
                    if (entity['carrier_code'] === carrierCode && entity['method_code'] === methodCode) {
                        shippingMethodTitle = entity['carrier_title'] + " - " + entity['method_title'];
                    }
                });
                return shippingMethodTitle;
            },
            getRateByCode : function(methodCodeParts) {
                var shippingRates = [],
                    shippingMethodCode = methodCodeParts.slice(0),
                    carrierCode = shippingMethodCode.shift(),
                    methodCode = shippingMethodCode.join('_');
                if (methodCodeParts) {
                    $.each(rates(), function (key, entity) {
                        if (entity['carrier_code'] === carrierCode && entity['method_code'] === methodCode) {
                            shippingRates.push(entity);
                        }
                    });
                }
                return shippingRates;
            }
        };
    }
);
