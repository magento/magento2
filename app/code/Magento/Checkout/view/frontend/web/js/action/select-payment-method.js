/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/action/get-totals'
], function (quote, messageContainer, setPaymentInformationAction, getTotalsAction) {
    'use strict';

    return function (paymentMethod) {
        if (paymentMethod) {
            paymentMethod.__disableTmpl = {
                title: true
            };
        }
        quote.paymentMethod(paymentMethod);
        setPaymentInformationAction(
            messageContainer,
            {
                method: paymentMethod.method
            }
        );

        if (quote.totals()['coupon_code']) {
            getTotalsAction([]);
        }
    };
});
