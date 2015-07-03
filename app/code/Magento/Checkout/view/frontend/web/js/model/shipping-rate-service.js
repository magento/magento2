/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
    ],
    function(defaultProcessor, customerAddressProcessor) {
        "use strict";
        var processors = [];
        processors['default'] =  defaultProcessor;
        processors['customer-address'] = customerAddressProcessor;

        return {
            registerProcessor: function(type, processor) {
                processors[type] = processor;
            },
            getRates: function (address) {
                var type = address.getType();
                var rates = [];
                if (processors[type]) {
                    rates = processors[type].getRates(address);
                } else {
                    rates = processors['default'].getRates(address);
                }
                return rates;
            }
        }
    }
);
