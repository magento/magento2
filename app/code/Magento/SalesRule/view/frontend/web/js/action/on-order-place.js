/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t,
             fullScreenLoader
) {
    'use strict';

    return function (paymentViewComponent, response, previousTotals, couponCode, isApplied) {
        let totals = quote.getTotals();

        if (parseInt(response.order_id) === 0) {
            messageContainer.clear();
            for (let i in response.errors) {
                paymentViewComponent.messageContainer.addErrorMessage(
                    {
                        message: response.errors[i]
                    }
                );
            }
            if (totals()) {
                let couponCodeStr = totals()['coupon_code'];

                if (couponCodeStr !== previousTotals['coupon_code']) {
                    paymentViewComponent.afterPlaceOrderCallbackResults.redirectAfterPlaceOrder = false;
                    if (couponCodeStr.length === 0) {
                        couponCode('');
                        isApplied(false);
                        messageContainer.addSuccessMessage({
                            'message': $t('The coupon code isn\'t valid. Verify the code and try again.')
                        });
                    } else {
                        couponCode(couponCodeStr);
                        isApplied(true);
                        messageContainer.addSuccessMessage({
                            'message': $t('Your coupon was successfully applied.')
                        });
                    }
                }
            }
        };
    }
});
