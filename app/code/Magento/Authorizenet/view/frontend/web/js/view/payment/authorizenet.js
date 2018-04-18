/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                type: 'authorizenet_directpost',
                component: 'Magento_Authorizenet/js/view/payment/method-renderer/authorizenet-directpost'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
