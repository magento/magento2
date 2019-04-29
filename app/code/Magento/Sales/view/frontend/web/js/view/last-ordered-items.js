/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore'
], function (Component, customerData, _) {
    'use strict';

    return Component.extend({
        defaults: {
            isShowAddToCart: false
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.lastOrderedItems = customerData.get('last-ordered-items');
            this.lastOrderedItems.subscribe(this.checkSalableItems.bind(this));
            this.checkSalableItems();

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('isShowAddToCart');

            return this;
        },

        /**
         * Check if items is_saleable and change add to cart button visibility.
         */
        checkSalableItems: function () {
            var isShowAddToCart = _.some(this.lastOrderedItems().items, {
                'is_saleable': true
            });

            this.isShowAddToCart(isShowAddToCart);
        }
    });
});
