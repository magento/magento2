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
        'Magento_Checkout/js/model/shipping-service',
        'mage/translate',
        'ko'
    ],
    function (quote, urlBuilder, navigator, shippingService, $t, ko) {
        "use strict";
        return function (code, customOptions, callbacks) {
            if (!code) {
                alert($t('Please specify a shipping method'));
                return;
            }

            var proceed = true;
            _.each(callbacks, function (callback) {
                proceed = proceed && callback();
            });

            if (proceed) {
                var shippingMethodCode = code.split("_"),
                    shippingRate = shippingService.getRateByCode(shippingMethodCode)[0];

                quote.setShippingMethod(shippingMethodCode);
                quote.setSelectedShippingMethod(code);
                quote.setShippingCustomOptions(customOptions);
                quote.setCollectedTotals('shipping', shippingRate.amount);
                navigator.setCurrent('shippingMethod').goNext();
            }
        };
    }
);
