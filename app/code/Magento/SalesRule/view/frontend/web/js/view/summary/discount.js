/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getCouponCode: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_code'];
        },

        /**
         * @return {*}
         */
        getCouponLabel: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_label'];
        },

        getTitle: function () {
            if (!this.totals()) {
                return null;
            }

            var total = this.totals();

            for(var i = 0, len = total.total_segments.length; i < len; i++) {
                if(total.total_segments[i].code == "discount") {
                    return total.total_segments[i].title;
                }
            }

            return null;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['discount_amount']) {
                price = parseFloat(this.totals()['discount_amount']);
            }

            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
