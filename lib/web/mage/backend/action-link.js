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

    $.widget('mage.actionLink', {
        /**
         * Button creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind handler on button click
         * @protected
         */
        _bind: function() {
            var keyCode = $.ui.keyCode;
            this._on({
                mousedown: function(e){
                    this._stopPropogation(e);
                },
                mouseup: function(e){
                    this._stopPropogation(e);
                },
                click: function(e) {
                    this._stopPropogation(e);
                    this._triggerEvent();
                },
                keydown: function(e) {
                    switch (e.keyCode) {
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            this._stopPropogation(e);
                            this._triggerEvent();
                            break;
                    }
                },
                keyup: function(e) {
                    switch (e.keyCode) {
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            this._stopPropogation(e);
                            break;
                    }
                }
            });
        },

        /**
         * @param {Object} e - event object
         * @private
         */
        _stopPropogation: function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();
        },

        /**
         * @private
         */
        _triggerEvent: function() {
            $(this.options.related || this.element)
                .trigger(this.options.event, this.options.eventData ? [this.options.eventData] : [{}]);
        }
    });

});
