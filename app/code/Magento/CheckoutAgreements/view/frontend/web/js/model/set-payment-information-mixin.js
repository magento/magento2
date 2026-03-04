/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_CheckoutAgreements/js/model/agreements-assigner'
], function ($, wrapper, agreementsAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            agreementsAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});
