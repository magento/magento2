/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['../model/quote',
     'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function(quote, shippingRateService) {
        "use strict";
        quote.shippingAddress.subscribe(function () {
            shippingRateService.getRates(quote.shippingAddress())
        });
        return function(shippingAddress) {
            quote.shippingAddress(shippingAddress);
            //set billing address same as shipping by default
            if (shippingAddress.canUseForBilling()) {
                quote.billingAddress(shippingAddress);
            }
        };
    }
);
