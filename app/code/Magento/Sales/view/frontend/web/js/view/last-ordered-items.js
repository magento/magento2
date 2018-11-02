/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            var isShowAddToCart;

            this._super();
            this.lastOrderedItems = customerData.get('last-ordered-items');

            isShowAddToCart = _.some(this.lastOrderedItems().items, {
                'is_saleable': true
            });

            this.lastOrderedItems.isShowAddToCart = isShowAddToCart;
        }
    });
});
