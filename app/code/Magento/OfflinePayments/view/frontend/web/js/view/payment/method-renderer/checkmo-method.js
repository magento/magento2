/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/payment/default'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/checkmo'
        },

        /** Returns send check to info */
        getMailingAddress: function () {
            return window.checkoutConfig.payment.checkmo.mailingAddress;
        },

        /** Returns payable to info */
        getPayableTo: function () {
            return window.checkoutConfig.payment.checkmo.payableTo;
        }
    });
});
