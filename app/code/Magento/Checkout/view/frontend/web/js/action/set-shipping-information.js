/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
