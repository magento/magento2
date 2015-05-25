/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        './customer-addresses'
    ],
    function(defaultProvider) {
        "use strict";
        var providers = [];
        providers.push(defaultProvider);

        return {
            registerProvider: function(provider) {
                providers.push(provider);
            },

            getItems: function() {
                var output = [];
                providers.forEach(function(provider) {
                    provider.getItems().forEach(function(addressItem) {
                        output.push(addressItem);
                    });
                });
                return output;
            }
        }
    }
);