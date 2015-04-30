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
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function (quote, urlBuilder, navigator, storage, errorList) {
        "use strict";
        return function (code, customOptions) {
            if (!code) {
                alert('Please specify a shipping method');
            }

            var shippingMethodCode = code.split("_");
            quote.setShippingMethod(shippingMethodCode);
            quote.setSelectedShippingMethod(code);
            quote.setShippingCustomOptions(customOptions);
            navigator.setCurrent('shippingMethod').goNext();
        };
    }
);
