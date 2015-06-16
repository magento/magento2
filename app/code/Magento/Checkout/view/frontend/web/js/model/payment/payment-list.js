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
        var data = ko.observableArray(defaultProvider.paymentMethods());
        defaultProvider.paymentMethods.subscribe(function () {
                data(defaultProvider.paymentMethods())
            }
        );
        return data;
    }
);
