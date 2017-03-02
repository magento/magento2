/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/subtotal'
        },

        /**
         * Get pure value.
         *
         * @return {*}
         */
        getPureValue: function () {
            var totals = quote.getTotals()();

            if (totals) {
                return totals.subtotal;
            }

            return quote.subtotal;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }

    });
});
