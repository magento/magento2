/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/actions/default'
            },
            placeOrder: function(parent) {
                return parent.placeOrder.bind(parent);
            }
        });
    }
);
