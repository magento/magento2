/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/action/get-totals'
], function (wrapper, quote, messageContainer, setPaymentInformationAction, getTotalsAction) {
    'use strict';

    return function (selectPaymentMethodAction) {

        return wrapper.wrap(selectPaymentMethodAction, function (originalSelectPaymentMethodAction, paymentMethod) {

            originalSelectPaymentMethodAction(paymentMethod);

            setPaymentInformationAction(
                messageContainer,
                {
                    method: paymentMethod.method
                }
            );

            getTotalsAction([]);
        });

    };

});
