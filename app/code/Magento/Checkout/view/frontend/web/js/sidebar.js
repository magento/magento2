/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/decorate"
], function($){

    $.widget('mage.sidebar', {
        options: {
            isRecursive: true,
            maxItemsVisible: 3,
            selectorItemQty: ':input.cart-item-qty',
            selectorItemButton: ':button.update-cart-item',
            selectorSummaryQty: 'div.content > div.items-total',
            selectorSubtotal: 'div.content > div.subtotal > div.amount span.price',
            selectorShowcartNumber: 'a.showcart > span.counter > span.counter-number',
            selectorShowcartLabel: 'a.showcart > span.counter > span.counter-label',
            selectorList: '#mini-cart'
        },

        _create: function() {
            var self = this;

            this.element.decorate('list', this.options.isRecursive);

            // Add event on "Go to Checkout" button click
            $(this.options.checkoutButton).on('click', $.proxy(function() {
                location.href = this.options.checkoutUrl;
            }, this));

            // Add event on "Close" button click
            $(this.options.closeButton).on('click', $.proxy(function() {
                $(this.options.targetElement).dropdownDialog("close");
            }, this));

            // Add event on "Remove item" click
            $(this.options.removeButton).click(function() {
                if (confirm(self.options.confirmMessage)) {
                    self._removeItem($(this));
                }
            });

            // Add event on "Qty" field changed
            $(this.options.selectorItemQty).change(function(event) {
                event.stopPropagation();
                self._showButton($(this));
            });

            // Add event on "Update Qty" button click
            $(this.options.selectorItemButton).click(function(event) {
                event.stopPropagation();
                self._updateQty($(this))
            });

            this._calcHeight();
        },

        _showButton: function(elem) {
            var itemId = elem.data('cart-item');
            $('#update-cart-item-' + itemId).show('fade', 300);
        },

        _hideButton: function(elem) {
            var itemId = elem.data('cart-item');
            $('#update-cart-item-' + itemId).hide('fade', 300);
        },

        _updateQty: function(elem) {
            var itemId = elem.data('cart-item');
            this._ajax(this.options.updateItemQtyUrl, {
                item_id: itemId,
                item_qty: $('#cart-item-' + itemId + '-qty').val()
            });
            this._hideButton(elem);
        },

        _removeItem: function(elem) {
            var itemId = elem.data('cart-item');
            this._ajax(this.options.removeItemUrl, {
                item_id: itemId
            })
        },

        /**
         * @param url - ajax url
         * @param data - post data for ajax call
         */
        _ajax: function(url, data) {
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                success: function (response) {
                    if (response.success && $.type(response.data) === 'object') {
                        this._refreshSummaryQty(response.data.summary_qty, response.data.summary_text);
                        this._refreshSubtotal(response.data.subtotal);
                        this._refreshShowcartCounter(response.data.summary_qty, response.data.summary_text);
                    } else {
                        var msg = response.error_message;
                        if (msg) {
                            window.alert($.mage.__(msg));
                        }
                    }
                },
                error: function (error) {
                    console.log(JSON.stringify(error));
                }
            });
        },

        _refreshSummaryQty: function(qty, text) {
            if (qty != undefined && text != undefined) {
                $(this.options.selectorSummaryQty).text(qty + text);
            }
        },

        _refreshSubtotal: function(val) {
            if (val != undefined) {
                $(this.options.selectorSubtotal).replaceWith(val);
            }
        },

        _refreshShowcartCounter: function(qty, text) {
            if (qty != undefined && text != undefined) {
                $(this.options.selectorShowcartNumber).text(qty);
                $(this.options.selectorShowcartLabel).text(text);
            }
        },

        _calcHeight: function() {
            var height = 0,
                counter = this.options.maxItemsVisible,
                target = $(this.options.selectorList)
                    .clone()
                    .attr('style', 'position: absolute !important; top: -10000 !important;')
                    .appendTo('body');

            target.children().each(function() {
                if (counter-- == 0) {
                    return false;
                }
                height += $(this).height() - 15;    // Fix height for each item!
            });

            target.remove();

            $(this.options.selectorList).css('height', height);
        }
    });

    return $.mage.sidebar;
});
