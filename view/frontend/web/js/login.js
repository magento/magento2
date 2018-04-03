/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
