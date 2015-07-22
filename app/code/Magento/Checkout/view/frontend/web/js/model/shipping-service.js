/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'ko'
    ],
    function (ko) {
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
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getSippingRates: function() {
                return shippingRates;
            }
        };
    }
);
