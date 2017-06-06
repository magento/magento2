/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/payment/paypal-express'
        }
    });
});
