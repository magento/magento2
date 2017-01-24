/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/totals',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, totals, Component, stepNavigator, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/cart-items'
            },
            totals: totals.totals(),
            items: ko.observable([]),
            maxCartItemsToDisplay: window.checkoutConfig.maxCartItemsToDisplay,
            cartUrl: window.checkoutConfig.cartUrl,

            /**
             * Component init
             */
            initialize: function () {
                var self = this;

                this._super();

                // Set initial items to observable field
                this.setItems(totals.getItems()());

                // Subscribe for items data changes and refresh items in view
                totals.getItems().subscribe(function (items) {
                    self.setItems(items);
                });
            },

            /**
             * Set items to observable field
             *
             * @param {Object} items
             */
            setItems: function (items) {

                if (items && items.length > 0) {
                    items = items.slice(-this.maxCartItemsToDisplay);
                }
                this.items(items);
            },

            /**
             * Returns cart items count
             *
             * @returns {Number}
             */
            getItemsCounter: function () {
                return parseFloat(this.totals['items_qty']);
            },

            /**
             * Returns bool value for items block state (expanded or not)
             *
             * @returns {*|Boolean}
             */
            isItemsBlockExpanded: function () {
                return quote.isVirtual() || stepNavigator.isProcessed('shipping');
            }
        });
    }
);
