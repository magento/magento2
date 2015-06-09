/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['ko', 'jquery'],
    function (ko, $) {
        "use strict";
        var shippingRates = ko.observableArray([]);
        return {
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function(ratesData) {
                shippingRates(ratesData);
                shippingRates.valueHasMutated();
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getSippingRates: function() {
                return shippingRates;
            },

            /**
             * Get shipping method title
             *
             * @param shippingMethod
             * @returns {string}
             */
            getTitleByCode: function(shippingMethod) {
                return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title : '';
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
