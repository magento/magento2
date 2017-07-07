/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
