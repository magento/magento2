/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/quote',
        'jquery'
    ],
    function (ko, selectShippingMethodAction, quote, $) {
        "use strict";
        var shippingRates = ko.observableArray([]);
        return {
            isLoading: ko.observable(false),
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function(ratesData) {
                shippingRates(ratesData);
                shippingRates.valueHasMutated();

                if (ratesData.length == 1) {
                    //set shipping rate if we have only one available shipping rate
                    selectShippingMethodAction(ratesData[0]);
                } else if(quote.shippingMethod()) {
                    var rateIsAvailable = ratesData.some(function (rate) {
                        if (rate.carrier_code == quote.shippingMethod().carrier_code
                            && rate.method_code == quote.shippingMethod().method_code) {
                            return true;
                        }
                        return false;
                    });
                    //Unset selected shipping shipping method if not available
                    if (!rateIsAvailable) {
                        selectShippingMethodAction(null);
                    }
                }
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
