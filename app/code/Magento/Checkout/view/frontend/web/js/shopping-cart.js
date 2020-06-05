/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'jquery-ui-modules/widget',
    'mage/translate'
], function ($, confirm) {
    'use strict';

    $.widget('mage.shoppingCart', {
        /** @inheritdoc */
        _create: function () {
            var items, i, reload, self = this;

            $(this.options.emptyCartButton).on('click', $.proxy(function () {
                confirm({
                    content: $.mage.__('Are you sure you want to remove all items from your Shopping Cart?'),
                    actions: {
                        confirm: function () {
                            $(self.options.emptyCartButton).attr('name', 'update_cart_action_temp');
                            $(self.options.updateCartActionContainer)
                                .attr('name', 'update_cart_action').attr('value', 'empty_cart');

                            if ($(self.options.emptyCartButton).parents('form').length > 0) {
                                $(self.options.emptyCartButton).parents('form').submit();
                            }
                        }
                    }
                });
            }, this));
            items = $.find('[data-role="cart-item-qty"]');

            for (i = 0; i < items.length; i++) {
                $(items[i]).on('keypress', $.proxy(function (event) { //eslint-disable-line no-loop-func
                    var keyCode = event.keyCode ? event.keyCode : event.which;

                    if (keyCode == 13) { //eslint-disable-line eqeqeq
                        $(this.options.emptyCartButton).attr('name', 'update_cart_action_temp');
                        $(this.options.updateCartActionContainer)
                            .attr('name', 'update_cart_action').attr('value', 'update_qty');

                    }
                }, this));
            }
            $(this.options.continueShoppingButton).on('click', $.proxy(function () {
                location.href = this.options.continueShoppingUrl;
            }, this));

            $(document).on('ajax:removeFromCart', $.proxy(function () {
                reload = true;
                $('div.block.block-minicart').on('dropdowndialogclose', $.proxy(function () {
                    if (reload === true) {
                        location.reload();
                        reload = false;
                    }
                    $('div.block.block-minicart').off('dropdowndialogclose');
                }));
            }, this));
            $(document).on('ajax:updateItemQty', $.proxy(function () {
                reload = true;
                $('div.block.block-minicart').on('dropdowndialogclose', $.proxy(function () {
                    if (reload === true) {
                        location.reload();
                        reload = false;
                    }
                    $('div.block.block-minicart').off('dropdowndialogclose');
                }));
            }, this));
        }
    });

    return $.mage.shoppingCart;
});
