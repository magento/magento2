/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(['jquery'], function($) {
    "use strict";
    var addresses = [];
    return {
        add: function (address) {
            addresses.push(address);
        },
        getAddressById: function(id) {
            var address = null;
            $.each(addresses, function(key, item) {
                if (id === item.customerAddressId) {
                    address = item;
                    return false;
                }
            });
            return address;
        },
        getAddresses: function() {
            return addresses.slice(0);
        },

        getDefaultShipping: function() {
            var address = null;
            if (addresses.length == 0) {
                return address;
            } else if (addresses.length == 1) {
                return addresses[0];
            }
            $.each(addresses, function(key, item) {
                if (item.isDefaultShipping) {
                    address = item;
                    return false;
                }
            });
            return address == null ? addresses[0] : address;
        }

    };
});
