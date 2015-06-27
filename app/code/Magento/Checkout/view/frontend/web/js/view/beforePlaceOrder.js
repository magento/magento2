/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['uiComponent'],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/iterator',
                displayArea: 'beforePlaceOrder'
            }
        });
    }
);
