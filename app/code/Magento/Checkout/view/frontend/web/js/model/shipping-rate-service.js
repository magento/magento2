/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
], function (quote, defaultProcessor, customerAddressProcessor) {
    'use strict';

    var processors = [];

    processors.default =  defaultProcessor;
    processors['customer-address'] = customerAddressProcessor;

    quote.shippingAddress.subscribe(function () {
        var type = quote.shippingAddress().getType();

        if (processors[type]) {
            processors[type].getRates(quote.shippingAddress());
        } else {
            processors.default.getRates(quote.shippingAddress());
        }
    });

    return {
        /**
         * @param {String} type
         * @param {*} processor
         */
        registerProcessor: function (type, processor) {
            processors[type] = processor;
        }
    };
});
