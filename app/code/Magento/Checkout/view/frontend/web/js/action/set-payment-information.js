/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
