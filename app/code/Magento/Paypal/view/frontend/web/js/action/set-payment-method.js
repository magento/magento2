/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-payment-information'
], function (quote, setPaymentInformation) {
    'use strict';

    return function (messageContainer) {
        return setPaymentInformation(messageContainer, quote.paymentMethod());
    };
});
