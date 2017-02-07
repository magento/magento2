/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    './customer-addresses'
], function (ko, defaultProvider) {
    'use strict';

    return ko.observableArray(defaultProvider.getAddressItems());
});
