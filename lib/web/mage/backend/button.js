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
/*global require:true*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    
    var resolver = require && require.resolver;

    $.widget('ui.button', $.ui.button, {
        options: {
            eventData: {},
            waitTillResolved: true
        },

        /**
         * Button creation.
         * @protected
         */
        _create: function() {
            if (this.options.event) {
                this.options.target = this.options.target || this.element;
                this._bind();
            }
            this._super();
        },

        /**
         * Bind handler on button click.
         * @protected
         */
        _bind: function() {
            var waitTillResolved = this.options.waitTillResolved,
                handler = !waitTillResolved || !resolver ? this._click : this._proxyClick;

            this.element
                .off( 'click.button' )
                .on( 'click.button', $.proxy(handler, this) );
        },

        /**
         * Button click handler.
         * @protected
         */
        _click: function(){
            var options = this.options;

            $(options.target).trigger(options.event, [options.eventData]);
        },

        /**
         * Proxy button click handler that might postpone the event
         * untill all of the rjs dependencies will be resolved. 
         * @protected
         */
        _proxyClick: function(){
            var options = this.options;

            if( resolver.resolved  ){
                this._click();
            }
            else if( !resolver.hasListeners('spinnerCover') ){
                $('body').trigger('processStart');

                resolver.on('spinnerCover', $.proxy(this._onResolve, this) );
            }
        },

        /**
         * Callback of the rjs resolver 'onAllResolved' event.
         * @protected
         */
        _onResolve: function(){
            $('body').trigger('processStop');

            this._click();
        }
    });
}));
