/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
        'Magento_Checkout/js/model/cart/totals-processor/default'
    ],
    function (quote, defaultProcessor, customerAddressProcessor, totalsDefaultProvider) {
        'use strict';

        var processors = [];

        if (quote.isVirtual()) {
            quote.shippingAddress.subscribe(function () {
                processors['default'] = totalsDefaultProvider;
                processors['default'] = totalsDefaultProvider.estimateTotals(quote.shippingAddress());

            });
        } else {
                quote.shippingAddress.subscribe(function () {
                    processors['default'] = defaultProcessor;
                    processors['customer-address'] = customerAddressProcessor;
                    var type = quote.shippingAddress().getType();
                    var rates = [];
                    if (processors[type]) {
                        rates = processors[type].getRates(quote.shippingAddress());
                    } else {
                        rates = processors['default'].getRates(quote.shippingAddress());
                    }
                });

        }
        return {
            registerProcessor: function(type, processor) {
                processors[type] = processor;
            }
        }
    }
);
