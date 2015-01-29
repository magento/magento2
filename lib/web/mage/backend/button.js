/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global require:true*/
(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui',
            'mage/requirejs/resolver'
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
        _create: function () {
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
        _bind: function () {
            var waitTillResolved = this.options.waitTillResolved,
                handler = !waitTillResolved || !resolver ? this._click : this._proxyClick;

            this.element
                .off('click.button')
                .on('click.button', $.proxy(handler, this));
        },

        /**
         * Button click handler.
         * @protected
         */
        _click: function () {
            var options = this.options;

            $(options.target).trigger(options.event, [options.eventData]);
        },

        /**
         * Proxy button click handler that might postpone the event
         * untill all of the rjs dependencies will be resolved.
         * @protected
         */
        _proxyClick: function () {
            if (resolver.resolved) {
                this._click();
            } else if (!resolver.hasListeners('spinnerCover')) {
                $('body').trigger('processStart');

                resolver.on('spinnerCover', $.proxy(this._onResolve, this));
            }
        },

        /**
         * Callback of the rjs resolver 'onAllResolved' event.
         * @protected
         */
        _onResolve: function () {
            $('body').trigger('processStop');

            this._click();
        }
    });

    return $.ui.button;
}));
