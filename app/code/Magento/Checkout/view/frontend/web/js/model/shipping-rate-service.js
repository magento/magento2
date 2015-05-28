/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    ['./shipping-rates-list-processor',
        'Magento_Customer/js/model/shipping-rates-list-processor'
    ],
    function(defaultProcessor, customerAddressProcessor) {
        "use strict";
        var processors = {};
        processors.default =  defaultProcessor;
        processors.customerAddress = customerAddressProcessor;

        return {
            registerProcessor: function(type, processor) {
                processors[type] = processor;
            },
            getRates: function (address) {
                var type = 'default';
                if (address.type) {
                    type = address.type;
                } else if (address.customerAddressId) {
                    type = 'customerAddress';
                }
                var rates = [];
                if (processors[type]) {
                    rates = processors[type].getRates(address);
                }
                return rates;
            }
        }
    }
);
