/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate',
    'mage/dropdown'
], function (Component, customerData, $, ko, _) {
    'use strict';

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        maxItemsToDisplay: window.checkout.maxItemsToDisplay,
        minicartSelector: '[data-block="minicart"]',
        addToCartCalls: 0,
        cart: {},

        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                this.addToCartCalls--;
                this.isLoading(this.addToCartCalls > 0);
                this.update(updatedCart);
                this.initSidebar();
            }, this);
            $(this.minicartSelector).on('contentLoading', function () {
                self.addToCartCalls++;
                self.isLoading(true);
            });

            if (
                cartData().website_id !== window.checkout.websiteId && cartData().website_id !== undefined ||
                cartData().storeId !== window.checkout.storeId && cartData().storeId !== undefined
            ) {
                customerData.reload(['cart'], false);
            }

            $(this.minicartSelector).on('dropdowndialogopen', function () {
                self.initSidebar();
            });

            return this._super();
        },
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        isLoading: ko.observable(false),

        /**
         * Initialize sidebar
         *
         * @return {Boolean}
         */
        initSidebar: function () {
            var miniCart = $(this.minicartSelector);

            if (!$('[data-role=product-item]').length) {
                return false;
            }

            miniCart.each(function () {
                var $element = $(this);

                if ($element.data('mageSidebar')) {
                    $element.sidebar('update');
                } else {
                    $element.sidebar({
                        'targetElement': 'div.block.block-minicart',
                        'url': {
                            'checkout': window.checkout.checkoutUrl,
                            'update': window.checkout.updateItemQtyUrl,
                            'remove': window.checkout.removeItemUrl,
                            'loginUrl': window.checkout.customerLoginUrl,
                            'isRedirectRequired': window.checkout.isRedirectRequired
                        },
                        'button': {
                            'checkout': '#top-cart-btn-checkout',
                            'remove': '#mini-cart a.action.delete',
                            'close': '#btn-minicart-close'
                        },
                        'showcart': {
                            'parent': 'span.counter',
                            'qty': 'span.counter-number',
                            'label': 'span.counter-label'
                        },
                        'minicart': {
                            'list': '#mini-cart',
                            'content': '#minicart-content-wrapper',
                            'qty': 'div.items-total',
                            'subtotal': 'div.subtotal span.price',
                            'maxItemsVisible': window.checkout.minicartMaxItemsVisible
                        },
                        'item': {
                            'qty': ':input.cart-item-qty',
                            'button': ':button.update-cart-item'
                        },
                        'confirmMessage': $.mage.__(
                            'Are you sure you would like to remove this item from the shopping cart?'
                        )
                    });
                }
                $element.trigger('contentUpdated');
            });
        },

        /**
         * Close mini shopping cart.
         */
        closeMinicart: function () {
            $(this.minicartSelector).find('[data-role="dropdownDialog"]').dropdownDialog('close');
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         *
         * @param {String} name
         * @returns {*}
         */
        getCartParamUnsanitizedHtml: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },

        /**
         * @deprecated please use getCartParamUnsanitizedHtml.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            return this.getCartParamUnsanitizedHtml(name);
        },

        /**
         * Returns array of cart items, limited by 'maxItemsToDisplay' setting
         * @returns []
         */
        getCartItems: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];

            items = items.slice(parseInt(-this.maxItemsToDisplay, 10));

            return items;
        },

        /**
         * Returns count of cart line items
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];

            return parseInt(items.length, 10);
        }
    });
});
