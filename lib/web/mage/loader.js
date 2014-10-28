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
/*global console:true*/
define([
    "jquery",
    "handlebars",
    "jquery/ui",
    "jquery/template",
    "mage/translate"
], function($){

    $.widget("mage.loader", {
        loaderStarted: 0,
        spinnerTemplate: $(undefined),
        options: {
            icon: '',
            texts: {
                loaderText: $.mage.__('Please wait...'),
                imgAlt: $.mage.__('Loading...')
            },
            template: '<script id="loader-template" type="text/x-handlebars-template">' +
                      '<div class="loading-mask" data-role="loader">' +
                      '<div class="loader">' +
                      '<img alt="{{imgAlt}}" src="{{icon}}">' +
                      '<p>{{loaderText}}</p>' +
                      '</div>' +
                      '</div>' +
                      '</script>'

        },

        /**
         * Loader creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind on ajax events
         * @protected
         */
        _bind: function() {
            this._on({
                'processStop': 'hide',
                'processStart': 'show',
                'show.loader': 'show',
                'hide.loader': 'hide',
                'contentUpdated.loader': '_contentUpdated'
            });
        },

        /**
         * Verify loader present after content updated
         *
         * This will be cleaned up by the task MAGETWO-11070
         *
         * @param event
         * @private
         */
        _contentUpdated: function(e) {
            this.show(e);
        },

        /**
         * Show loader
         */
        show: function(e, ctx) {
            this._render();
            this.loaderStarted++;
            this.spinner.show();
            if (ctx) {
                this.spinner
                    .css({width: ctx.outerWidth(), height: ctx.outerHeight(), position: 'absolute'})
                    .position({
                        my: 'top left',
                        at: 'top left',
                        of: ctx
                    });
            }
            return false;
        },

        /**
         * Hide loader
         */
        hide: function() {
            if (this.loaderStarted > 0) {
                this.loaderStarted--;
                if (this.loaderStarted === 0) {
                    this.spinner.hide();
                }
            }
            return false;
        },

        /**
         * Render loader
         * @protected
         */
        _render: function() {
            if (this.spinnerTemplate.length === 0) {
                this.spinnerTemplate = $(this.options.template)/*.css(this._getCssObj())*/;
                var source = this.spinnerTemplate.html();
                var template = Handlebars.compile(source);
                var content = {
                    imgAlt: this.options.texts.imgAlt,
                    icon: this.options.icon,
                    loaderText: this.options.texts.loaderText
                };
                var html = $(template(content));
                html.prependTo(this.element);
                this.spinner = html;
            }
        },

        /**
         * Destroy loader
         */
        _destroy: function() {
            this.spinner.remove();
        }
    });

    /**
     * This widget takes care of registering the needed loader listeners on the body
     */
    $.widget("mage.loaderAjax", {
        options: {
            defaultContainer: '[data-container=body]',
            loadingClass: 'ajax-loading'
        },
        _create: function() {
            this._bind();
            // There should only be one instance of this widget, and it should be attached
            // to the body only. Having it on the page twice will trigger multiple processStarts.
            if (window.console && !this.element.is(this.options.defaultContainer) && $.mage.isDevMode(undefined)) {
                console.warn("This widget is intended to be attached to the body, not below.");
            }
        },
        _bind: function() {
            this._on(this.options.defaultContainer, {
                'ajaxSend': '_onAjaxSend',
                'ajaxComplete': '_onAjaxComplete'
            });
        },
        _getJqueryObj: function(loaderContext) {
            var ctx;
            // Check to see if context is jQuery object or not.
            if (loaderContext) {
                if (loaderContext.jquery) {
                    ctx = loaderContext;
                } else {
                    ctx = $(loaderContext);
                }
            } else {
                ctx = $('[data-container="body"]');
            }
            return ctx;
        },
        _onAjaxSend: function(e, jqxhr, settings) {
            $(this.options.defaultContainer).addClass(this.options.loadingClass);

            if (settings && settings.showLoader) {
                var ctx = this._getJqueryObj(settings.loaderContext);
                ctx.trigger('processStart');

                // Check to make sure the loader is there on the page if not report it on the console.
                // NOTE that this check should be removed before going live. It is just an aid to help
                // in finding the uses of the loader that maybe broken.
                if (window.console && !ctx.parents('[data-role="loader"]').length) {
                    console.warn('Expected to start loader but did not find one in the dom');
                }
            }
        },
        _onAjaxComplete: function(e, jqxhr, settings) {
            $(this.options.defaultContainer).removeClass(this.options.loadingClass);
            if (settings && settings.showLoader) {
                this._getJqueryObj(settings.loaderContext).trigger('processStop');
            }
        }

    });

});
