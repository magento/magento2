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
        quote.getShippingAddress().subscribe(function () {
            shippingRateService.getRates(quote.getShippingAddress()())
        });
        return function(shippingAddress) {
            quote.setShippingAddress(shippingAddress);
        };
    }
);
