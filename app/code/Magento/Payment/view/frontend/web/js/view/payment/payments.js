/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'free',
            component: 'Magento_Payment/js/view/payment/method-renderer/free-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
