/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        'Magento_Checkout/js/model/shipping-service',
        'mage/translate'
    ],
    function (quote) {
        "use strict";
        return function (shippingMethod) {
            quote.shippingMethod(shippingMethod)
        }
    }
);
