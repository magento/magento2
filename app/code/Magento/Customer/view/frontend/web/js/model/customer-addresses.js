/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    './customer/address'
], function ($, ko, Address) {
    'use strict';

    var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

    return {
        /**
         * @return {Array}
         */
        getAddressItems: function () {
            var items = [],
                customerData = window.customerData;

            if (isLoggedIn()) {
                if (Object.keys(customerData).length) {
                    $.each(customerData.addresses, function (key, item) {
                        items.push(new Address(item));
                    });
                }
            }

            return items;
        }
    };
});
