/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
