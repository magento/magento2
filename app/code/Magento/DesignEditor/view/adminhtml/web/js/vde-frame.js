/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){

    /**
     * Widget vde frame
     */
    $.widget('vde.vdeFrame', {
        options: {
            vdeToolbar: null,
            vdePanel: null
        },

        _create: function () {
            this._bind();
            this._initFrame();
        },

        _bind: function() {
            $(window).on('resize', $.proxy(this._resizeFrame, this));
            $('body').on('refreshIframe', $.proxy(this._refreshFrame, this));
            this.element.on('load', function() {
                $('body').trigger('processStop');
            });
        },

        /**
         * Calculate and set frame height
         *
         * @private
         */
        _resizeFrame: function() {
            var windowHeight = $(window).innerHeight(),
                vdeToolbarHeight = $(this.options.vdeToolbar).outerHeight(true),
                vdePanelHeight = $(this.options.vdePanel).outerHeight(true),
                frameHeight = windowHeight - vdeToolbarHeight - vdePanelHeight;

            this.element.height(frameHeight);
        },

        /**
         * Reload frame
         *
         * @private
         */
        _refreshFrame: function() {
            $('body').trigger('processStart');

            this.element[0].contentWindow.location.reload(true);
        },

        /**
         * Initialize frame
         *
         * @private
         */
        _initFrame: function() {
            this._resizeFrame();
        }
    });

});