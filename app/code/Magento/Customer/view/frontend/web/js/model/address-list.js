/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
