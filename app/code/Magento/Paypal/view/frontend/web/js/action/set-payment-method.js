/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information'
], function ($, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, setPaymentInformation) {
    'use strict';

    return function (messageContainer) {
        return setPaymentInformation(messageContainer, quote.paymentMethod());
    };
});
