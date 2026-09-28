/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/shipping',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isCalculated: function () {
            return !!quote.shippingMethod();
        },

        /**
         * @override
         */
        getShippingMethodTitle: function () {
            return '(' + this._super() + ')';
        }
    });
});
