/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/action/place-order'
    ],
    function (Component, placeOrderAction) {
        'use strict';

        return Component.extend({
            /**
             * Place order.
             */
            placeOrder: function () {
                var data = {
                    "method": this.item.code,
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
                };
                placeOrderAction(data);
            },
            /**
             * Get payment method type.
             */
            getTitle: function () {
                return this.item.title;
            },

            /**
             * Get payment method code.
             */
            getCode: function () {
                return this.item.code;
            }
        });
    }
);
