/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated since version 2.2.0
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
