/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (ko, defaultProvider) {
        'use strict';

        return ko.observableArray(defaultProvider.getAvailablePaymentMethods());
    }
);
