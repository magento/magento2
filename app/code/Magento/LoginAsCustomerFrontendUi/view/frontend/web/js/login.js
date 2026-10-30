/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
