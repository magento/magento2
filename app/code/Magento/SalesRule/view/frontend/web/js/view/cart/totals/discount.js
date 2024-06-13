/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_SalesRule/js/view/summary/discount'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/cart/totals/discount'
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() != 0; //eslint-disable-line eqeqeq
        }
    });
});
