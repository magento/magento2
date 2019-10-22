/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
    'Magento_SalesRule/js/action/on-order-place',
    'Magento_Checkout/js/model/payment/after-place-order-callbacks'
], function (
    $,
    ko,
    Component,
    quote,
    setCouponCodeAction,
    cancelCouponAction,
    coupon,
    onOrderPlaceAction,
    orderPlaceCallbacks
) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);


    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: couponCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        initialize: function () {
            this._super();
            orderPlaceCallbacks.push(this.afterOrderPlaced);
        },

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         *
         * @param object paymentView
         * @param object response
         * @returns {*}
         */
        afterOrderPlaced: function (paymentView, response, previousTotals) {
            return onOrderPlaceAction(paymentView, response, previousTotals, couponCode, isApplied);
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
