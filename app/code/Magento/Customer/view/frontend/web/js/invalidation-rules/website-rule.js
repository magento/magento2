/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "underscore"
], function (_) {
    'use strict';

    return {
        /**
         * Takes website id from current customer data and compare it with current website id
         * If customer belongs to another scope, we need to invalidate current section
         *
         * @param {Object} customerData
         */
        process: function (customerData) {
            var customer = customerData.get('customer'),
                scopeConfig = window.scopeConfig;

            if (scopeConfig && customer && customer.websiteId != scopeConfig.websiteId) {
                customerData.invalidate(['customer']);
            }
        }
    }
});
