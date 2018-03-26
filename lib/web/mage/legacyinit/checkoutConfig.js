(function () {
    'use strict';

    window.checkoutConfig = JSON.parse(document.getElementById('legacyJS_checkoutConfig').textContent).checkoutConfig;
    // Create aliases for customer.js model from customer module
    window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;
    window.customerData = window.checkoutConfig.customerData;
}());
