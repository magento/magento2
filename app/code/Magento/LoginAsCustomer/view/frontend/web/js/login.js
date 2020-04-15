/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'Magento_Customer/js/customer-data'
], function (customerData) {

    'use strict';

    return function (config) {
        customerData.reload('customer').done(function () {
            window.location.href = config.redirectUrl;
        });
    };
});
