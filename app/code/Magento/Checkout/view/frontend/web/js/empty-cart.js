/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    customerData.reload(['cart'], false);
});
