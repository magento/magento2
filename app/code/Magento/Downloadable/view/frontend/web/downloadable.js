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
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui"
], function($){

    $.widget('mage.downloadable', {
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
            var price = 0,
                oldPrice = 0,
                inclTaxPrice = 0,
                exclTaxPrice = 0;
            this.element.find(this.options.linkElement + ':checked').each($.proxy(function(index, element) {
                price += this.options.config.links[$(element).val()].price;
                oldPrice += this.options.config.links[$(element).val()].oldPrice;
                inclTaxPrice += this.options.config.links[$(element).val()].inclTaxPrice;
                exclTaxPrice += this.options.config.links[$(element).val()].exclTaxPrice;
            }, this));

            this.element.trigger('changePrice', {
                'config': 'config',
                'price': {
                    'price': price,
                    'oldPrice': oldPrice,
                    'inclTaxPrice': inclTaxPrice,
                    'exclTaxPrice': exclTaxPrice
                }
            }).trigger('reloadPrice');
        }
    });
});