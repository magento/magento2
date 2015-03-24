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
            selectorItemQty: ':input.cart-item-qty',
            selectorItemButton: ':button.update-cart-item',
            selectorSummaryQty: 'div.content > div.items-total',
            selectorSubtotal: 'div.content > div.subtotal > div.amount',
            selectorShowcartNumber: 'a.showcart > span.counter > span.counter-number',
            selectorShowcartLabel: 'a.showcart > span.counter > span.counter-label'
        },

        _create: function() {
            this.element.decorate('list', this.options.isRecursive);

            $(this.options.checkoutButton).on('click', $.proxy(function() {
                location.href = this.options.checkoutUrl;
            }, this));

            // TODO:
            $(this.options.removeButton).on('click', $.proxy(function() {
                return confirm(this.options.confirmMessage);
            }, this));

            $(this.options.closeButton).on('click', $.proxy(function() {
                $(this.options.targetElement).dropdownDialog("close");
            }, this));

            // TODO:
            var self = this;

            $(this.options.selectorItemQty).change(function(event) {
                event.stopPropagation();
                self._showButton($(this));
            });

            $(this.options.selectorItemButton).click(function(event) {
                event.stopPropagation();
                self._updateQty($(this))
            });
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
            this._ajaxUpdate(this.options.updateItemQtyUrl, {
                item_id: itemId,
                item_qty: $('#cart-item-' + itemId + '-qty').val()
            });
            this._hideButton(elem);
        },

        /**
         *
         * @param url - ajax url
         * @param data - post data for ajax call
         */
        _ajaxUpdate: function(url, data) {
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
        }
    });

    return $.mage.sidebar;
});
