/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        itemTemplates: [],
        isPossibleCheckout: true,
        checkoutUrl: window.checkout.checkoutUrl,
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
        },
        getItemTemplate: function (itemType) {
            console.log(itemType);
        }
    });
});
