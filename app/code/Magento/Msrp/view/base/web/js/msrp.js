/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/dropdown"
], function($){
    'use strict';

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
                    if (this.options.addToCartUrl) {
                        $(this.options.cartForm).attr('action', this.options.addToCartUrl);
                    }
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
            }
            $(this.options.cartForm).submit();
        }
    });
    
    return $.mage.addToCart;
});
