/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'braintree',
                component: 'Magento_Braintree/js/view/payment/method-renderer/cc-form'
            },
            {
                type: 'braintree_paypal',
                component: 'Magento_Braintree/js/view/payment/method-renderer/braintree-paypal'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
