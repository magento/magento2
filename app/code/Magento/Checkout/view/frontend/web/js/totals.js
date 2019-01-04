/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/url',
    'Magento_Ui/js/block-loader'
], function (url, blockLoader) {
    'use strict';

    return function (config) {
        window.checkoutConfig = config.checkoutConfig;
        window.customerData = window.checkoutConfig.customerData;
        window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;

        blockLoader(config.loaderFile);
        url.setBaseUrl(config.baseUrl);
    };
});
