/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    return function (paymentMethod) {
        if (paymentMethod) {
            paymentMethod.__disableTmpl = {
                title: true
            };
        }
        quote.paymentMethod(paymentMethod);
    };
});
