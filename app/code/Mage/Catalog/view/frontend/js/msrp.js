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

/*jshint evil:true browser:true jquery:true*/
(function($) {
    $.widget('mage.addToCart', {
        options: {
            groupedProductContainer: '.grouped-items-table',
            showAddToCart: true
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
                    $('#map-popup-heading').text(this.options.productName);
                    $('#map-popup-price').html($(this.options.realPrice));
                    $('#map-popup-msrp').html(this.options.msrpPrice);
                    this.element.trigger('reloadPrice');
                    var width = $('#map-popup').width();
                    var offsetX = e.pageX - (width / 2) + "px";
                    $('#map-popup').css({left: offsetX, top: e.pageY}).show();
                    if (!this.options.showAddToCart) {
                        $('#map-popup-content > .map-popup-checkout').hide();
                    }
                    $('#map-popup-content').show();
                    $('#map-popup-text').addClass('map-popup-only-text').show();
                    $('#map-popup-text-what-this').hide();
                    return false;
                }
            }, this));

            $(this.options.helpLinkId).on('click', $.proxy(function(e) {
                $('#map-popup-heading').text(this.options.productName);
                var width = $('#map-popup').width();
                var offsetX = e.pageX - (width / 2) + "px";
                $('#map-popup').css({left: offsetX, top: e.pageY}).show();
                $('#map-popup-content').hide();
                $('#map-popup-text').hide();
                $('#map-popup-text-what-this').show();
                return false;
            }, this));

            $(this.options.closeButtonId).on('click', $.proxy(function() {
                $('#map-popup').hide();
                return false;
            }, this));

        },

        _addToCartSubmit: function() {
            this.element.trigger('addToCart', this.element);
            if (this.options.addToCartUrl) {
                $('#map-popup').hide();
                if (opener !== null) {
                    opener.location.href = this.options.addToCartUrl;
                } else {
                    location.href = this.options.addToCartUrl;
                }

            } else if (this.options.cartForm) {
                $(this.options.cartForm).submit();
            }
        }
    });
})(jQuery);

