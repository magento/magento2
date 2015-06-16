/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/action/place-order'
    ],
    function (Component, placeOrderAction) {
        return Component.extend({
            defaults: {
                template: 'Magento_OfflinePayments/payment/checkmo',
                type: 'checkmo'
            },
            placeOrder: function() {
                var data = {
                    "method": this.index,
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
                };
                placeOrderAction(data);
            }
        });
    }
);
