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
define(["jquery","jquery/ui"], function($){

    $.widget('mage.float', {
        options: {
            productOptionsSelector: '#product-options-wrapper'
        },

        /**
         * Bind handlers to scroll event
         * @private
         */
        _create: function() {
            $(window).on('scroll', $.proxy(this._setTop, this));
        },

        /**
         * float bundleSummary on windowScroll
         * @private
         */
        _setTop: function() {
            if ((this.element).is(':visible')) {
                var starTop = $(this.options.productOptionsSelector).offset().top,
                    offset = $(document).scrollTop(),
                    maxTop = this.element.parent().offset().top;
                if (!this.options.top) {
                    this.options.top = this.element.position().top;
                    this.element.css('top', this.options.top);
                }

                if (starTop > offset) {
                    return false;
                }

                if (offset < this.options.top) {
                    offset = this.options.top;
                }

                var allowedTop = this.options.top + offset - starTop;

                if (allowedTop < maxTop) {
                    this.element.css('top', allowedTop);
                }
            }
        }
    });
});