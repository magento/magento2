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
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";
    
    $.widget('mage.relatedProducts', {
        options: {
            relatedCheckbox: '.related-checkbox', // Class name for a related product's input checkbox.
            relatedProductsCheckFlag: false, // Related products checkboxes are initially unchecked.
            relatedProductsField: '#related-products-field', // Hidden input field that stores related products.
            selectAllMessage: $.mage.__('select all'),
            unselectAllMessage: $.mage.__('unselect all'),
            selectAllLink: "[role='select-all']",
            elementsSelector: ".item.product"
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            $(this.options.selectAllLink).on('click', $.proxy(this._selectAllRelated, this));
            $(this.options.relatedCheckbox).on('click', $.proxy(this._addRelatedToProduct, this));
            this._showRelatedProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle')
            );
        },

        /**
         * This method either checks all checkboxes for a product's set of related products (select all)
         * or unchecks them (unselect all).
         * @private
         * @param e - Click event on either the "select all" link or the "unselect all" link.
         * @return {Boolean} - Prevent default event action and event propagation.
         */
        _selectAllRelated: function(e) {
            var innerHTML = this.options.relatedProductsCheckFlag ?
                this.options.selectAllMessage : this.options.unselectAllMessage;
            $(e.target).html(innerHTML);
            $(this.options.relatedCheckbox).attr('checked',
                this.options.relatedProductsCheckFlag = !this.options.relatedProductsCheckFlag);
            this._addRelatedToProduct();
            return false;
        },

        /**
         * This method iterates through each checkbox for all related products and collects only those products
         * whose checkbox has been checked. The selected related products are stored in a hidden input field.
         * @private
         */
        _addRelatedToProduct: function() {
            $(this.options.relatedProductsField).val(
                $(this.options.relatedCheckbox + ':checked').map(function() {
                    return this.value;
                }).get().join(',')
            );
        },

        /**
         * Show related products according to limit. Shuffle if needed.
         * @param elements
         * @param limit
         * @param shuffle
         * @private
         */
        _showRelatedProducts: function(elements, limit, shuffle) {
            if (shuffle) {
                this._shuffle(elements);
            }
            if (limit === 0) {
                limit = elements.length;
            }
            for (var index = 0; index < limit; index++) {
                $(elements[index]).show();
            }
        },

        /**
         * Shuffle an array
         * @param o
         * @returns {*}
         */
        _shuffle: function shuffle(o){ //v1.0
            for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
            return o;
        }
    });

    return $.mage.relatedProducts;
});