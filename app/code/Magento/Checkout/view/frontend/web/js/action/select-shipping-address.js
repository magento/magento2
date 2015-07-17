/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        '../model/quote',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function(quote, shippingRateService, selectBillingAddress) {
        'use strict';
        quote.shippingAddress.subscribe(function () {
            shippingRateService.getRates(quote.shippingAddress())
        });
        return function(shippingAddress) {
            quote.shippingAddress(shippingAddress);
            //set billing address same as shipping by default if it is not empty
            if (shippingAddress.countryId != undefined && shippingAddress.canUseForBilling()) {
                selectBillingAddress(quote.shippingAddress());
            }
        };
    }
);
