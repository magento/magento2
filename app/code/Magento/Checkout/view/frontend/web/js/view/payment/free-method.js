/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/generic'
    ],
    function (generic) {
        return generic.extend({
            defaults: {
                displayArea: 'freeMethod'
            }
        });
    }
);
