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

});