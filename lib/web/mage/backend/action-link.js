/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.actionLink', {
        /**
         * Button creation
         * @protected
         */
        _create: function () {
            this._bind();
        },

        /**
         * Bind handler on button click
         * @protected
         */
        _bind: function () {
            var keyCode = $.ui.keyCode;

            this._on({
                /**
                 * @param {jQuery.Event} e
                 */
                mousedown: function (e) {
                    this._stopPropogation(e);
                },

                /**
                 * @param {jQuery.Event} e
                 */
                mouseup: function (e) {
                    this._stopPropogation(e);
                },

                /**
                 * @param {jQuery.Event} e
                 */
                click: function (e) {
                    this._stopPropogation(e);
                    this._triggerEvent();
                },

                /**
                 * @param {jQuery.Event} e
                 */
                keydown: function (e) {
                    switch (e.keyCode) {
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            this._stopPropogation(e);
                            this._triggerEvent();
                            break;
                    }
                },

                /**
                 * @param {jQuery.Event} e
                 */
                keyup: function (e) {
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
        _stopPropogation: function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();
        },

        /**
         * @private
         */
        _triggerEvent: function () {
            $(this.options.related || this.element)
                .trigger(this.options.event, this.options.eventData ? [this.options.eventData] : [{}]);
        }
    });

    return $.mage.actionLink;
});
