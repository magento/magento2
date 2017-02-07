/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/shipping-save-processor/default'
], function (defaultProcessor) {
    'use strict';

    var processors = [];

    processors['default'] =  defaultProcessor;

    return {
        /**
         * @param {String} type
         * @param {*} processor
         */
        registerProcessor: function (type, processor) {
            processors[type] = processor;
        },

        /**
         * @param {String} type
         * @return {Array}
         */
        saveShippingInformation: function (type) {
            var rates = [];

            if (processors[type]) {
                rates = processors[type].saveShippingInformation();
            } else {
                rates = processors['default'].saveShippingInformation();
            }

            return rates;
        }
    };
});
