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
/*jshint browser:true jquery:true */
define([
    "jquery",
    "jquery/ui"
], function($){

    $.widget('mage.redirectUrl', {
        options: {
            event: 'click',
            url: undefined
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function() {
            var handlers = {};
            handlers[this.options.event] = '_onEvent';
            this._on(handlers);
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function() {
            this._bind();
        },

        /**
         * This method set the url for the redirect.
         * @private
         */
        _onEvent: function(){
            if (this.options.url) {
                location.href = this.options.url;
            } else {
                location.href = this.element.val();
            }
        }
    });

});