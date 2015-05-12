/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        '../model/step-navigator',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function (quote, urlBuilder, navigator, shippingService) {
        "use strict";
        return function (code, customOptions) {
            if (!code) {
                alert('Please specify a shipping method');
            }

            var shippingMethodCode = code.split("_"),
                shippingRate = shippingService.getRateByCode(shippingMethodCode)[0];

            quote.setShippingMethod(shippingMethodCode);
            quote.setSelectedShippingMethod(code);
            quote.setShippingCustomOptions(customOptions);
            quote.setCollectedTotals('shipping', shippingRate.amount);
            navigator.setCurrent('shippingMethod').goNext();
        };
    }
);
