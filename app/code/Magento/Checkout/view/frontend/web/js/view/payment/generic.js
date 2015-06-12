/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/place-order'
    ],
    function (Component, quote, placeOrderAction) {
        return Component.extend({
            getCode: function() {
                return this.index;
            },
            isActive: function(parent) {
                return false;
            },
            getData: function() {
                return {};
            },
            getInfo: function() {
                return [];
            },
            afterSave: function() {
                return true;
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
