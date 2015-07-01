/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'ko',
        './customer-addresses'
    ],
    function(ko, defaultProvider) {
        "use strict";
        return ko.observableArray(defaultProvider.getAddressItems());
    }
);