/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/totals'
            }
        });
    }
);
