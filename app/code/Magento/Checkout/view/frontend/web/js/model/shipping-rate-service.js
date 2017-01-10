/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
    ],
    function (quote, defaultProcessor, customerAddressProcessor) {
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
            registerProcessor: function (type, processor) {
                processors[type] = processor;
            }
        }
    }
);
