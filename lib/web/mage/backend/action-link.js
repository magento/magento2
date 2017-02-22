/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
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
    
    return $.mage.actionLink;
});
