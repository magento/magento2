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
        'mage/translate'
    ],
    function (quote, urlBuilder, navigator, $t) {
        "use strict";
        return function (code, customOptions) {
            if (!code) {
                alert($t('Please specify a shipping method'));
                return;
            }

            var shippingMethodCode = code.split("_");
            quote.setShippingMethod(shippingMethodCode);
            quote.setSelectedShippingMethod(code);
            quote.setShippingCustomOptions(customOptions);
            navigator.setCurrent('shippingMethod').goNext();
        };
    }
);
