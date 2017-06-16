/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/minicart/subtotal/totals',
    'underscore'
], function (Component, _) {
    'use strict';

    return Component.extend({

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.displaySubtotal(this.isMsrpApplied(this.cart().items));
            this.cart.subscribe(function (updatedCart) {

                this.displaySubtotal(this.isMsrpApplied(updatedCart.items));
            }, this);
        },

        /**
         * Determine if subtotal should be hidden.
         * @param {Array} cartItems
         * @return {Boolean}
         */
        isMsrpApplied: function (cartItems) {
            return !_.find(cartItems, function (item) {
                if (_.has(item, 'canApplyMsrp')) {
                    return item.canApplyMsrp;
                }

                return false;
            });
        }
    });
});
