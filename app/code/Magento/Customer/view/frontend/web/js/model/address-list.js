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

    let customerAddresses = ko.observableArray([]).extend({ deferred: true });
    let customerAddressesDataArray = customerAddresses();

    let addressItems = defaultProvider.getAddressItems();
    let mappedAddresses = addressItems ? addressItems.map(address => address) : [];

    ko.utils.arrayPushAll(
        customerAddressesDataArray,
        mappedAddresses
    );

    customerAddresses.valueHasMutated();

    return customerAddresses;
});
