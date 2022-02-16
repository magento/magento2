/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function () {
        var customer;

        // When the session is available, this customer menu will be available
        if ($('.customer-menu').length > 0) {
            customer = customerData.get('customer');

            customerData.getInitCustomerData().done(function () {
                // Check if the customer data is set in local storage, if not reload data from server
                if (!customer().firstname) {
                    customerData.reload([], false);
                }
            });
        }
    };
});
