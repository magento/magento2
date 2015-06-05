/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/action/get-totals'
    ],
    function (Component, action) {
        "use strict";
        action();
        return Component.extend({
            defaults: {
                displayArea: 'summary',
                template: 'Magento_Checkout/summary'
            },
            title: 'Order Summary',
            getTotals: function() {
                action();
            }
        });
    }
);
