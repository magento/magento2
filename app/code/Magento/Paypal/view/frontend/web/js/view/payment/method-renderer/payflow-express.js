/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/payment/payflow-express'
        }
    });
});
