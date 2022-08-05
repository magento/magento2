/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/coupon'
], function (wrapper, quote, coupon) {
    'use strict';

    return function (shippingSaveProcessor) {
        shippingSaveProcessor.saveShippingInformation = wrapper.wrapSuper(
            shippingSaveProcessor.saveShippingInformation,
            function (type) {
                var updateCouponCallback;

                /**
                 * Update coupon form
                 */
                updateCouponCallback = function () {
                    if (quote.totals() && !quote.totals()['coupon_code']) {
                        coupon.setCouponCode('');
                        coupon.setIsApplied(false);
                    }
                };

                return this._super(type).done(updateCouponCallback);
            }
        );

        return shippingSaveProcessor;
    };
});
