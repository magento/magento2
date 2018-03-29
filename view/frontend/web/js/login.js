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
