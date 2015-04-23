/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data'
], function (Component, _, customerData) {
    'use strict';

    return Component.extend({
        checkoutUrl: window.checkout.checkoutUrl,
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
        },
        getItemTemplate: function (item) {
            if (_.has(this.itemRenderer, item.product_type)) {
                return this.itemRenderer[item.product_type];
            }
            return this.itemRenderer['default'];
        }
    });
});

