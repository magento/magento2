/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/grand-total'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isDisplayed: function () {
            return true;
        }
    });
});
