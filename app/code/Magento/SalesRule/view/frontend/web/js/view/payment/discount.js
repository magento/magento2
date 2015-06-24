/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon'
    ],
    function (ko, Component, quote, setCouponCodeAction, cancelCouponAction) {
        'use strict';
        var totals = quote.getTotals();
        var couponCode = ko.observable(null);
        if (totals()) {
            couponCode(totals()['coupon_code']);
        }
        var isApplied = ko.observable(couponCode() != null);
        return Component.extend({
            defaults: {
                template: 'Magento_SalesRule/payment/discount'
            },
            couponCode: couponCode,
            /**
             * Applied flag
             */
            isApplied: isApplied,
            isLoading: false,
            apply: function() {
                setCouponCodeAction(couponCode(), isApplied);
            },
            cancel: function() {
                cancelCouponAction(isApplied);
                this.couponCode = '';
                couponCode('');
            }
        });
    }
);
