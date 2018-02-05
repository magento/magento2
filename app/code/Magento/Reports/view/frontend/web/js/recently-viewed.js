/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.recentlyViewedProducts', {
        options: {
            localStorageKey: "recently-viewed-products",
            productBlock: "#widget_viewed_item",
            viewedContainer: "ol"
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function() {
            var productHtml = $(this.options.productBlock).html();
            var productSku = $(this.options.productBlock).data('sku');
            var products = JSON.parse(window.localStorage.getItem(this.options.localStorageKey));
            if (products) {
                var productsLength = products['sku'].length;
                var maximum = $(this.element).data('count');
                var showed = 0;
                for (var index = 0; index <= productsLength; index++) {
                    if (products['sku'][index] == productSku || showed >= maximum) {
                        products['sku'].splice(index, 1);
                        products['html'].splice(index, 1);
                    } else {
                        $(this.element).find(this.options.viewedContainer).append(products['html'][index]);
                        $(this.element).show();
                        showed++;
                    }
                }
                $(this.element).find(this.options.productBlock).show();
            } else {
                products = {};
                products['sku'] = [];
                products['html'] = [];
            }
            products['sku'].unshift(productSku);
            products['html'].unshift(productHtml);
            window.localStorage.setItem(this.options.localStorageKey, JSON.stringify(products));
        }
    });
    
    return $.mage.recentlyViewedProducts;
});
