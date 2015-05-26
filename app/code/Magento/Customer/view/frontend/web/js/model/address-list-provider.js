/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/addresslist',
        './customer-addresses'
    ],
    function(addressList, defaultProvider) {
        "use strict";
        defaultProvider.getItems().forEach(function (item) {
            addressList.add(item);
        });

        return {
            registerProvider: function(provider) {
                provider.getItems().forEach(function (item) {
                    addressList.add(item);
                });
            },

            getItems: function() {
                return addressList.getAddresses();
            }
        }
    }
);