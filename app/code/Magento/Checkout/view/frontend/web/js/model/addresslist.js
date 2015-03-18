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
                if (id == item.id) {
                    address = item;
                    return false;
                }
            });
            return address;
        },
        getAddresses: function() {
            return addresses;
        }
    }
});
