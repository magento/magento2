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

    return ko.observableArray(defaultProvider.getAddressItems());
});
