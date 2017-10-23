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
            var isShowAddToCart = false,
                item;

            this._super();
            this.lastOrderedItems = customerData.get('last-ordered-items');

            for (item in this.lastOrderedItems.items) {
                if (item['is_saleable']) {
                    isShowAddToCart = true;
                    break;
                }
            }

            this.lastOrderedItems.isShowAddToCart = isShowAddToCart;
        }
    });
});
