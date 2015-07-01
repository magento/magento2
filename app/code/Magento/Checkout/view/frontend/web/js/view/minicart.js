/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'mage/url'
], function (Component, customerData, $, ko, url) {
    'use strict';

    var sidebarInitialized = false;
    url.setBaseUrl(window.checkout.baseUrl);

    function initSidebar() {
        var minicart = $("[data-block='minicart']");
        minicart.trigger('contentUpdated');
        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        minicart.mage('sidebar', {
            "targetElement": "div.block.block-minicart",
            "url": {
                "checkout": window.checkout.checkoutUrl,
                "update": window.checkout.updateItemQtyUrl,
                "remove": window.checkout.removeItemUrl
            },
            "button": {
                "checkout": "#top-cart-btn-checkout",
                "remove": "#mini-cart a.action.delete",
                "close": "#btn-minicart-close"
            },
            "showcart": {
                "parent": "span.counter",
                "qty": "span.counter-number",
                "label": "span.counter-label"
            },
            "minicart": {
                "list": "#mini-cart",
                "content": "#minicart-content-wrapper",
                "qty": "div.items-total",
                "subtotal": "div.subtotal span.price"
            },
            "item": {
                "qty": ":input.cart-item-qty",
                "button": ":button.update-cart-item"
            },
            "confirmMessage": $.mage.__(
                'Are you sure you want to remove this item from your Compare Products list?'
            )
        });
    }

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
            this.cart.subscribe(function () {
                sidebarInitialized = false;
            });
        },
        initSidebar: ko.observable(initSidebar),
        closeSidebar: function(element) {
            var minicart = $('[data-block="minicart"]');
            minicart.on('click', '[data-action="close"]', function(event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog("close");
            });
            return true;
        },
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        }
    });
});
