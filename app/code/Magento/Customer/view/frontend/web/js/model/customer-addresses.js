/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        './customer/address'
    ],
    function($, ko, address) {
        "use strict";

        var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

        return {
            getAddressItems: function() {
                var items = [];
                if (isLoggedIn()) {
                    var customerData = window.customerData;
                    if (Object.keys(customerData).length) {
                        $.each(customerData.addresses, function (key, item) {
                            items.push(new address(item));
                        });
                    }
                }

                return items;
            }
        }
    }
);
