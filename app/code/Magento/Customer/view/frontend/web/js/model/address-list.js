/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    './customer-addresses'
], function (ko, defaultProvider) {
    'use strict';

    let customerAddresses = ko.observableArray([]).extend({
            deferred: true
        });
    let customerAddressesDataArray = customerAddresses();
    let addressItems = defaultProvider.getAddressItems();

    ko.utils.arrayPushAll(
        customerAddressesDataArray,
        addressItems
    );

    customerAddresses.valueHasMutated();

    return customerAddresses;
});
