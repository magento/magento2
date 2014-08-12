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
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){

    $.widget('mage.addToWishlist', {
        options: {
            bundleInfo: '[id^=bundle-option-]',
            configurableInfo: '.super-attribute-select',
            groupedInfo: '#super-product-table input',
            downloadableInfo: '#downloadable-links-list input',
            customOptionsInfo: '.product-custom-option',
            qtyInfo: '#qty'
        },
        _create: function () {
            this._bind();
        },
        _bind: function() {
            var changeCustomOption = 'change ' + this.options.customOptionsInfo,
                changeQty = 'change ' + this.options.qtyInfo,
                changeProductInfo = 'change ' + this.options[this.options.productType + 'Info'],
                events = {};
            events[changeCustomOption] = '_updateWishlistData';
            events[changeProductInfo] = '_updateWishlistData';
            events[changeQty] = '_updateWishlistData';
            this._on(events);
        },
        _updateWishlistData: function(event) {
            var dataToAdd = {};
            $(event.handleObj.selector).each(function(index, element){
                dataToAdd[$(element).attr('name')] = $(element).val();
            });

            $('[data-action="add-to-wishlist"]').each(function(index, element) {
                var params = $(element).data('post');
                params.data = $.extend({}, params.data, dataToAdd);
                $(element).data('post', params);
            });
        }
    });

});