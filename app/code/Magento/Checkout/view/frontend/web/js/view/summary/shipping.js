/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/view/summary/discount'
], function ($, Component, quote, discountView) {
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
            var shippingMethod = '',
                shippingMethodTitle = '';

            if (!this.isCalculated()) {
                return '';
            }
            shippingMethod = quote.shippingMethod();

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodTitle = ' - ' + shippingMethod['method_title'];
            }

            return shippingMethod ?
                shippingMethod['carrier_title'] + shippingMethodTitle :
                shippingMethod['carrier_title'];
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
        },

        /**
         * If is set coupon code, but there wasn't displayed discount view.
         *
         * @return {Boolean}
         */
        haveToShowCoupon: function () {
            var couponCode = this.totals()['coupon_code'];

            if (typeof couponCode === 'undefined') {
                couponCode = false;
            }

            return couponCode && !discountView().isDisplayed();
        },

        /**
         * Returns coupon code description.
         *
         * @return {String}
         */
        getCouponDescription: function () {
            if (!this.haveToShowCoupon()) {
                return '';
            }

            return '(' + this.totals()['coupon_code'] + ')';
        }
    });
});
