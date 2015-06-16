/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (ko, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_OfflinePayments/payment/banktransfer'
            },
            isChecked: ko.observable(false),
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstruction: function () {
                return window.checkoutConfig.payment.instructions[this.item.code];
            }
        });
    }
);
