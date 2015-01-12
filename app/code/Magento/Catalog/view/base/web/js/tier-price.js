/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.tierPrice', {
        options: {
            popupHeading: '#map-popup-heading',
            productForm: '#product_addtocart_form',
            popupPrice: '#map-popup-price',
            popupMsrp: '#map-popup-msrp',
            popup: '#map-popup',
            popupContent: '#map-popup-content',
            popupText: '#map-popup-text',
            popupOnlyText: 'map-popup-only-text',
            popupTextWhatThis: '#map-popup-text-what-this',
            popupCartButtonId: '#map-popup-button'
        },

        _create: function() {
            this.element.on('click', '[data-tier-price]', $.proxy(this._showTierPrice, this));
        },

        /**
         * Show tier price popup on gesture
         * @private
         * @param e - element got the clicked on
         * @return {Boolean}
         */
        _showTierPrice: function(e) {
            var data = $(e.target).data('tier-price');
            $(this.options.popupCartButtonId).off('click');
            $(this.options.popupCartButtonId).on('click', $.proxy(function() {
                this.element.find(this.options.inputQty).val(data.qty);
                this.element.find(this.options.productForm).submit();
            }, this));
            $(this.options.popupHeading).text(data.name);
            $(this.options.popupPrice).html($(data.price)).find('[id^="product-price-"]').attr('id', function() {
                // change price element id, so price option won't update the tier price
                return 'tier' + $(this).attr('id');
            });
            $(this.options.popupMsrp).html(data.msrp);
            var width = $(this.options.popup).width();
            var offsetX = e.pageX - (width / 2) + "px";
            $(this.options.popup).css({left: offsetX, top: e.pageY}).addClass('active').show();
            $(this.options.popupContent).show();
            $(this.options.popupText).addClass(this.options.popupOnlyText).show();
            $(this.options.popupTextWhatThis).hide();
            return false;
        }
    });
    
    return $.mage.tierPrice;
});