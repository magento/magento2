/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/totals',
                displayArea: 'totals'
            }
        });
    }
);
