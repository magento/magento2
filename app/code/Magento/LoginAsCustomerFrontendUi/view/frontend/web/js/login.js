/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/section-config'
], function ($, customerData, sectionConfig) {

    'use strict';

    return function (config) {
        customerData.reload(sectionConfig.getSectionNames()).done(function () {
            window.location.href = config.redirectUrl;
        });
    };
});
