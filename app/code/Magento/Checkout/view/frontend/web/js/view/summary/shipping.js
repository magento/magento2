/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function ($, Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/shipping'
        },
        quoteIsVirtual: quote.isVirtual(),
        totals: quote.getTotals(),

        /**
         * @return {*}
         */
        getShippingMethodTitle: function () {
            var shippingMethod,
                shippingMethodTitle,
                shippingTitle = '';

            if (!this.isCalculated()) {
                return '';
            }
            shippingMethod = quote.shippingMethod();

            if (typeof shippingMethod['method_title'] !== 'undefined'
                && shippingMethod['method_title'].trim().length !== 0) {
                shippingMethodTitle = shippingMethod['method_title'];
            }

            if(typeof shippingMethod['carrier_title'] !== 'undefined'
                && shippingMethod['carrier_title'].trim().length !== 0){
                shippingTitle = shippingMethod['carrier_title'] + ' - ';
            }

            return shippingMethod ? shippingTitle + shippingMethodTitle : '';
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price =  this.totals()['shipping_amount'];

            return this.getFormattedPrice(price);
        }
    });
});
