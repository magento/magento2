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
    $.widget('mage.slide', {
        options: {
            slideSpeed: 1500,
            slideSelector: '#bundle-slide',
            slideBackSelector: '.bundle-slide-back',
            bundleProductSelector: '#bundleProduct',
            bundleOptionsContainer: '#options-container',
            productViewContainer: '#productView',
            slidedown: true

        },

        _create: function() {
            if(this.options.slidedown === true) {
                $(this.options.slideSelector).on('click', $.proxy(this._show, this));
                $(this.options.slideBackSelector).on('click', $.proxy(this._hide, this));
                this.options.autostart && this._show();
            } else {
                $(this.options.slideSelector).on('click', $.proxy(this._slide, this));
                $(this.options.slideBackSelector).on('click', $.proxy(this._slideBack, this));
                this.options.autostart && this._slide();
            }
        },

        /**
         * slide bundleOptionsContainer over to the main view area
         * @private
         */
        _slide: function() {
            $(this.options.bundleProductSelector).css('top', '0px');
            $(this.options.bundleOptionsContainer).show();
            this.element.css('height',$(this.options.productViewContainer).height() + 'px');
            $(this.options.bundleProductSelector).css('left', '0px').animate(
                {'left': '-' + this.element.width() + 'px'},
                this.options.slideSpeed,
                $.proxy(function() {
                    this.element.css('height','auto');
                    $(this.options.productViewContainer).hide();
                }, this)
            );
        },

        /**
         * slideback productViewContainer to main view area
         * @private
         */
        _slideBack: function() {
            $(this.options.bundleProductSelector).css('top', '0px');
            $(this.options.productViewContainer).show();
            this.element.css('height', $(this.options.bundleOptionsContainer).height() + 'px');
            $(this.options.bundleProductSelector).animate(
                {'left': '0px'},
                this.options.slideSpeed,
                $.proxy(function() {
                    $(this.options.bundleOptionsContainer).hide();
                    this.element.css('height','auto');
                }, this)
            );
        },
        _show: function() {
            $(this.options.bundleOptionsContainer).slideDown(800);
            $('html, body').animate({
                scrollTop: $(this.options.bundleOptionsContainer).offset().top
            }, 600);
        },
        _hide: function() {
            $('html, body').animate({
                scrollTop: 0
            }, 600);
            $(this.options.bundleOptionsContainer).slideUp(800);
        }
    });
});