/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    '../model/quote',
    'Magento_Checkout/js/model/shipping-save-processor'
], function (quote, shippingSaveProcessor) {
    'use strict';

    return function () {
        return shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType());
    };
});
