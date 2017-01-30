/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
