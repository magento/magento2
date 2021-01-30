/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Checkout/js/action/get-totals'
], function ($, wrapper, quote, coupon, getTotalsAction) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            var result;

            $.when(
                result = originalAction(paymentData, messageContainer)
            ).fail(
                function () {
                    var deferred = $.Deferred(),

                        /**
                         * Update coupon form
                         */
                        updateCouponCallback = function () {
                            if (quote.totals() && !quote.totals()['coupon_code']) {
                                coupon.setCouponCode('');
                                coupon.setIsApplied(false);
                            }
                        };

                    getTotalsAction([], deferred);
                    $.when(deferred).done(updateCouponCallback);
                }
            );

            return result;
        });
    };
});
