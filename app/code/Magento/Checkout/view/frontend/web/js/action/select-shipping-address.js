/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-billing-address'
], function (quote,checkoutData,selectBillingAddress) {
    'use strict';
    return function (shippingAddress) {
        window.isbothAddressSame = false;
        quote.shippingAddress(shippingAddress);
        quote.billingAddress(quote.billingAddress());
    };
});
