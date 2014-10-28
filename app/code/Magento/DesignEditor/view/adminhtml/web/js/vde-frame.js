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