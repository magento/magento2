/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_Checkout/js/action/get-totals',
    'Magento_SalesRule/js/model/coupon'
], function ($, wrapper, quote, messageContainer, setPaymentInformationExtended, getTotalsAction, coupon) {
    'use strict';

    return function (selectPaymentMethodAction) {

        return wrapper.wrap(selectPaymentMethodAction, function (originalSelectPaymentMethodAction, paymentMethod) {

            originalSelectPaymentMethodAction(paymentMethod);

            if (paymentMethod === null) {
                return;
            }

            $.when(
                setPaymentInformationExtended(
                    messageContainer,
                    {
                        method: paymentMethod.method
                    },
                    true
                )
            ).done(
                function () {
                    var deferred = $.Deferred(),

                        /**
                         * Update coupon form.
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
        });
    };

});
