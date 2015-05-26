/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'uiComponent'
    ],
    function (quote, Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/cart_items',
                displayArea: 'columns'
            }
        });
    }
);
