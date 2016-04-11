/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    var agreementsConfig = window.checkoutConfig.checkoutAgreements;

    return function (placeOrderAction) {
        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function(originalAction, paymentData, redirectOnSuccess, messageContainer) {
            if (!agreementsConfig.isEnabled) {
                return originalAction(paymentData, redirectOnSuccess, messageContainer);
            }

            var agreementForm = $('.payment-method._active form[data-role=checkout-agreements]'),
                agreementData = agreementForm.serializeArray(),
                agreementIds = [];

            agreementData.forEach(function(item) {
                agreementIds.push(item.value);
            });

            paymentData.extension_attributes = {agreement_ids: agreementIds};
            return originalAction(paymentData, redirectOnSuccess, messageContainer);
        });
    };
});
