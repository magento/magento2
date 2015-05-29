/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(['jquery', 'ko'], function($, ko) {
    "use strict";
    var addresses = ko.observableArray();
    return {
        /**
         * Add new address to list. Id address already exist => replace it
         * @param newAddress
         */
        add: function (newAddress) {
            var existingAddress = this.getAddressById(newAddress.customerAddressId);
            if (existingAddress !== null) {
                this.replaceAddress(existingAddress, newAddress);
            } else {
                addresses.push(newAddress);
            }
        },
        /**
         * Replace existing address with new one
         * @param existingAddress
         * @param newAddress
         */
        replaceAddress: function(existingAddress, newAddress) {
            this.removeAddress(existingAddress);
            addresses.push(newAddress);
        },
        /**
         * Remove address from list
         * @param address
         */
        removeAddress: function(address) {
            $.each(addresses(), function(key, item) {
                if (item.hasOwnProperty('customerAddressId') && address.customerAddressId === item.customerAddressId) {
                    addresses.splice(key, 1);
                }
            });
        },
        /**
         * Get address by customerAddressId
         * @param id
         * @returns {*}
         */
        getAddressById: function(id) {
            var address = null;
            $.each(addresses(), function(key, item) {
                if (item.hasOwnProperty('customerAddressId') && id === item.customerAddressId) {
                    address = item;
                    return false;
                }
            });
            return address;
        },
        /**
         * Returns array of addresses
         * @returns {observableArray}
         */
        getAddresses: function() {
            return addresses;
        },
        /**
         * Returns customer default shipping address
         * @returns {*}
         */
        getDefaultShipping: function() {
            var address = {"customerAddressId": null};
            if (addresses().length == 0) {
                return address;
            } else if (addresses().length == 1) {
                return addresses()[0];
            }
            $.each(addresses(), function(key, item) {
                if (item.isDefaultShipping) {
                    address = item;
                    return false;
                }
            });
            return address == null ? addresses()[0] : address;
        },
        /**
         * Refresh address list
         */
        refreshList: function() {
            addresses.valueHasMutated();
        },
        /**
         * Hard refresh address list
         */
        refreshListHard: function() {
            var data = addresses().slice(0);
            addresses([]);
            addresses(data);
        }
    };
});
