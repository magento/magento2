/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

define(['Magento_Customer/js/customer-data'], function (customerData) {
    'use strict';

    return function () {
        var cartData = customerData.get('cart');

        customerData.getInitCustomerData().done(function () {
            if (cartData().items && cartData().items.length !== 0) {
                customerData.reload(['cart'], false);
            }
        });
    };
});
