/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiClass'
], function (Element) {
    'use strict';

    return Element.extend({

        defaults: {
            scopeConfig: {}
        },

        /**
         * Takes website id from current customer data and compare it with current website id
         * If customer belongs to another scope, we need to invalidate current section
         *
         * @param {Object} customerData
         */
        process: function (customerData) {
            var customer = customerData.get('customer');

            if (this.scopeConfig && customer() &&
                ~~customer().websiteId !== ~~this.scopeConfig.websiteId && ~~customer().websiteId !== 0) {
                customerData.reload(['customer']);
            }
        }
    });
});
