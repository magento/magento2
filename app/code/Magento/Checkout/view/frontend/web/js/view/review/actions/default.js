/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/review/actions/default'
        },

        /**
         * @param {Object} parent
         * @return {Function}
         */
        placeOrder: function (parent) {
            return parent.placeOrder.bind(parent);
        }
    });
});
