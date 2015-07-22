/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function(quote, selectBillingAddress) {
        'use strict';
        return function(shippingAddress) {
            quote.shippingAddress(shippingAddress);
            //set billing address same as shipping by default if it is not empty
            if (shippingAddress.countryId != undefined && shippingAddress.canUseForBilling()) {
                selectBillingAddress(quote.shippingAddress());
            }
        };
    }
);
