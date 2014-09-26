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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
define(["jquery", "jquery/ui", "mage/dropdown"], function($) {
    $.widget('mage.addToCart', {
        options: {
            showAddToCart: true,
            cartForm: '.form.map.checkout'
        },

        _create: function() {
            $(this.options.cartButtonId).on('click', $.proxy(function() {
                this._addToCartSubmit();
            }, this));

            $(this.options.popupId).on('click', $.proxy(function(e) {
                if (this.options.submitUrl) {
                    location.href = this.options.submitUrl;
                } else {
                    $(this.options.popupCartButtonId).off('click');
                    $(this.options.popupCartButtonId).on('click', $.proxy(function() {
                        this._addToCartSubmit();
                    }, this));
                    $('#map-popup-heading-price').text(this.options.productName);
                    $('#map-popup-price').html($(this.options.realPrice));
                    $('#map-popup-msrp > span.price').html(this.options.msrpPrice);
                    this.element.trigger('reloadPrice');
                    var dialog = $("#map-popup-click-for-price");
                    this._popupDialog(dialog, this.options.popupId);
                    if (!this.options.showAddToCart) {
                        $('#product_addtocart_form_from_popup').hide();
                    }
                    return false;
                }
            }, this));

            $(this.options.helpLinkId).on('click', $.proxy(function(e) {
                $('#map-popup-heading-what-this').text(this.options.productName);
                var dialog = $("#map-popup-what-this");
                this._popupDialog(dialog, this.options.helpLinkId);
                return false;
            }, this));
        },

        _popupDialog: function(target, trigger) {
            if (!target.hasClass('ui-dialog-content')) {
                target.dropdownDialog({
                    appendTo: ".column.main",
                    dialogContentClass: 'active',
                    timeout: "2000",
                    autoPosition: true,
                    "dialogClass": "popup"
                });
            }
            $('.mage-dropdown-dialog > .ui-dialog-content').dropdownDialog("close");
            target.dropdownDialog("option", "position", {my: "right+50% top", collision: "none", at: "center bottom", of: trigger});
            target.dropdownDialog("option", "triggerTarget", trigger);
            target.dropdownDialog("open");

        },

        _addToCartSubmit: function() {
            this.element.trigger('addToCart', this.element);
            if (this.options.addToCartButton) {
                $(this.options.addToCartButton).click();
                return;
            }
            if (this.options.addToCartUrl) {
                $('.mage-dropdown-dialog > .ui-dialog-content').dropdownDialog("close");
                $(this.options.cartForm).attr('action', this.options.addToCartUrl);
            }
            $(this.options.cartForm).submit();
        }
    });
});
