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
            if (addresses.indexOf(this.newAddress) == -1) {
                this.add(this.newAddress);
            }
            return addresses;
        },
        newAddress: {
            getFullAddress: function() {
                return 'New Address';
            },
            customerAddressId: null
        }
    };
});
