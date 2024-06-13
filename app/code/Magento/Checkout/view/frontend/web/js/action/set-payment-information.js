/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/action/set-payment-information-extended'

], function (setPaymentInformationExtended) {
    'use strict';

    return function (messageContainer, paymentData) {

        return setPaymentInformationExtended(messageContainer, paymentData, false);
    };
});
