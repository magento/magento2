/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
