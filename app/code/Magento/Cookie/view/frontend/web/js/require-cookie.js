/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint evil:true browser:true jquery:true */
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.requireCookie', {
        options: {
            event: 'click',
            noCookieUrl: 'enable-cookies',
            triggers: ['.action.login', '.action.submit']
        },

        /**
         * Constructor
         * @private
         */
        _create: function() {
            this._bind();
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function() {
            var events = {};

            $.each(this.options.triggers, function(index, value) {
                events['click ' + value] = '_checkCookie';
            });
            this._on(events);
        },

        /**
         * This method set the url for the redirect.
         * @private
         */
        _checkCookie: function(event) {
            if (navigator.cookieEnabled) {
                return;
            }
            event.preventDefault();
            window.location = this.options.noCookieUrl;
        }
    });

    return $.mage.requireCookie;
});
