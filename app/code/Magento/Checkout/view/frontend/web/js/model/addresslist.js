/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery'], function($) {
    var addresses = [];
    return {
        add: function (address) {
            addresses.push(address);
        },
        getAddressById: function(id) {
            var address = null;
            $.each(addresses, function(key, item) {
                if (id == item.customerAddressId) {
                    address = item;
                    return false;
                }
            });
            return address;
        },
        getAddresses: function() {
            if (addresses.indexOf(this.newAddress) !== -1) {
                return addresses;
            } else {
                addresses.push(this.newAddress);
                return addresses;
            }
        },
        newAddress: {
            getFullAddress: function() {
                return 'New Address';
            },
            customerAddressId: null
        }
    }
});
