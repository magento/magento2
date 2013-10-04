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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $.widget('mage.addToWishlist', {
        options: {
            bundleInfo: '[id^=bundle-option-]',
            configurableInfo: '.super-attribute-select',
            groupedInfo: '#super-product-table input',
            downloadableInfo: '.options-list input',
            customOptionsInfo: '.product-custom-option'
        },
        _create: function () {
            this.addToWishlist();
        },
        addToWishlist: function () {
            this._on({
                'click [data-action="add-to-wishlist"]': function (event) {
                    var url = $(event.target).closest('a').attr('href'),
                        productInfo = this.options[this.options.productType + 'Info'],
                        additionalData = $(this.options.customOptionsInfo).serialize();
                    if (productInfo !== undefined) {
                        additionalData += $(productInfo).serialize();
                    }
                    $(event.target).closest('a').attr('href', url + (url.indexOf('?') == -1 ? '?' : '&') + additionalData);
                }
            });
        }
    });
})(jQuery);
