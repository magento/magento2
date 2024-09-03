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
    'Magento_SalesRule/js/model/coupon'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: coupon.getCouponCode(),

        /**
         * Applied flag
         */
        isApplied: coupon.getIsApplied(),

        initialize: function () {
            var totals = quote.getTotals(),
                couponCode = this.couponCode,
                isApplied = this.isApplied,
                couponCodeValue;

            if (totals()) {
                couponCode(totals()['coupon_code']);
            }

            couponCodeValue = couponCode();
            isApplied(typeof couponCodeValue === 'string' && couponCodeValue.length > 0);

            this._super();
        },

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(this.couponCode(), this.isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                this.couponCode('');
                cancelCouponAction(this.isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            let form = '#discount-form';

            $(form + ' input[type="text"]').each(function () {
                let currentValue = $(this).val();

                $(this).val(currentValue.trim());
            });
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
