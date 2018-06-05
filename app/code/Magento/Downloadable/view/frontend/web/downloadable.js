/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui",
    "Magento_Catalog/js/price-box"
], function($){
    "use strict";
    
    $.widget('mage.downloadable', {
        options: {
            priceHolderSelector: '.price-box'
        },

        _create: function() {
            var self = this;

            this.element.find(this.options.linkElement).on('change', $.proxy(function() {
                this._reloadPrice();
            }, this));

            this.element.find(this.options.allElements).on('change', function() {
                if (this.checked) {
                    $('label[for="' + this.id + '"] > span').text($(this).attr('data-checked'));
                    self.element.find(self.options.linkElement + ':not(:checked)').each(function(){
                        $(this).trigger('click');
                    });
                } else {
                    $('[for="' + this.id + '"] > span').text($(this).attr('data-notchecked'));
                    self.element.find(self.options.linkElement + ':checked').each(function(){
                        $(this).trigger('click');
                    });
                }
            });
        },

        /**
         * Reload product price with selected link price included
         * @private
         */
        _reloadPrice: function() {
            var finalPrice = 0;
            var basePrice = 0;
            this.element.find(this.options.linkElement + ':checked').each($.proxy(function(index, element) {
                finalPrice += this.options.config.links[$(element).val()].finalPrice;
                basePrice += this.options.config.links[$(element).val()].basePrice;
            }, this));

            $(this.options.priceHolderSelector).trigger('updatePrice', {
                'prices': {
                    'finalPrice': { 'amount': finalPrice },
                    'basePrice': { 'amount': basePrice }
                }
            });
        }
    });
    
    return $.mage.downloadable;
});
