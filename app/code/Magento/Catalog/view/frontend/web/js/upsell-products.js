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
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.upsellProducts', {
        options: {
            elementsSelector: ".item.product"
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            this._showUpsellProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle')
            );
        },

        /**
         * Show upsell products according to limit. Shuffle if needed.
         * @param elements
         * @param limit
         * @param shuffle
         * @private
         */
        _showUpsellProducts: function(elements, limit, shuffle) {
            if (shuffle) {
                this._shuffle(elements);
            }
            if (limit === 0) {
                limit = elements.length;
            }
            for (var index = 0; index < limit; index++) {
                $(this.element).find(elements[index]).show();
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

    return $.mage.upsellProducts;
});