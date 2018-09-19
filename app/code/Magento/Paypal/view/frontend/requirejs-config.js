/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            orderReview: 'Magento_Paypal/js/order-review',
            paypalCheckout: 'Magento_Paypal/js/paypal-checkout'
        }
    },
    paths: {
        paypalInContextExpressCheckout: 'https://www.paypalobjects.com/api/checkout'
    },
    shim: {
        paypalInContextExpressCheckout: {
            exports: 'paypal'
        }
    }
};
