/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    'use strict';

    return function (payload) {
        payload.addressInformation['extension_attributes'] = {};

        return payload;
    };
});
