/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    frontend product msrp
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*jshint browser:true jquery:true*/
(function($, window) {
    "use strict";
    $.widget('mage.addWishListToCart', {

        options: {
            dataAttribute: 'item-id',
            nameFormat: 'qty[{0}]',
            wishListFormSelector: '#wishlist-view-form',
            btnRemoveSelector: '.btn-remove',
            qtySelector: '.qty',
            addToCartSelector: '.btn-cart',
            addAllToCartSelector: '.btn-add',
            commentInputType: 'textarea'
        },

        /**
         * Bind handlers to events
         */
        _create: function() {
            $(this.options.wishListFormSelector)
                .on('submit', $.proxy(this._addItemsToCart, this))
                .on('click', this.options.addToCartSelector, $.proxy(this._addItemsToCart, this))
                .on('click', this.options.btnRemoveSelector, $.proxy(this._confirmRemoveWishlistItem, this))
                .on('click', this.options.addAllToCartSelector, $.proxy(this._addAllWItemsToCart, this))
                .on('focusin focusout', this.options.commentInputType, $.proxy(this._focusComment, this));
        },

        /**
         * Validate and Redirect
         * @private
         * @param {string} url
         */
        _validateAndRedirect: function(url) {
            if ($(this.options.wishListFormSelector).validation({
                errorPlacement: function(error, element) {
                    error.insertAfter(element.next());
                }
            }).valid()) {
                $(this.options.wishListFormSelector).prop('action', url);
                window.location.href = url;
            }
        },

        /**
         * Add items to cart
         * @private
         * @param {event} e
         */
        _addItemsToCart: function() {
            $(this.options.addToCartSelector).each($.proxy(function(index, element) {
                if ($(element).data(this.options.dataAttribute)) {
                    var itemId = $(element).data(this.options.dataAttribute),
                        url = this.options.addToCartUrl.replace('%item%', itemId),
                        inputName = $.validator.format(this.options.nameFormat, itemId),
                        inputValue = $(this.options.wishListFormSelector).find('[name="' + inputName + '"]').val(),
                        separator = (url.indexOf('?') >= 0) ? '&' : '?';
                    url += separator + inputName + '=' + encodeURIComponent(inputValue);
                    this._validateAndRedirect(url);
                    return;
                }
            }, this));
        },

        /**
         * Confirmation window for removing wish list item
         * @private
         */
        _confirmRemoveWishlistItem: function() {
            return window.confirm(this.options.confirmRemoveMessage);
        },

        /**
         * Add all wish list items to cart
         * @private
         */
        _addAllWItemsToCart: function() {
            var url = this.options.addAllToCartUrl;
            var separator = (url.indexOf('?') >= 0) ? '&' : '?';
            $(this.options.wishListFormSelector).find(this.options.qtySelector).each(
                function(index, elem) {
                    url += separator + $(elem).prop('name') + '=' + encodeURIComponent($(elem).val());
                    separator = '&';
                }
            );

            this._validateAndRedirect(url);
        },

        /**
         * Toggle comment string
         * @private
         * @param {event} e
         */
        _focusComment: function(e) {
            var commentInput = e.currentTarget;
            if (commentInput.value === '' || commentInput.value === this.options.commentString) {
                commentInput.value = commentInput.value === this.options.commentString ? '' : this.options.commentString;
            }
        }
    });
})(jQuery, window);
