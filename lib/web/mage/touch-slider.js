/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jquery-ui-modules/slider'
], function ($, _) {
    'use strict';

    /**
     * Adds support for touch events for regular jQuery UI slider.
     */
    $.widget('mage.touchSlider', $.ui.slider, {

        /**
         * Creates instance of widget.
         *
         * @override
         */
        _create: function () {
            _.bindAll(
                this,
                '_mouseDown',
                '_mouseMove',
                '_onTouchEnd'
            );

            return this._superApply(arguments);
        },

        /**
         * Initializes mouse events on element.
         * @override
         */
        _mouseInit: function () {
            var result = this._superApply(arguments);

            this.element
                .off('mousedown.' + this.widgetName)
                .on('touchstart.' + this.widgetName, this._mouseDown);

            return result;
        },

        /**
         * Elements' 'mousedown' event handler polyfill.
         * @override
         */
        _mouseDown: function (event) {
            var prevDelegate = this._mouseMoveDelegate,
                result;

            event = this._touchToMouse(event);
            result = this._super(event);

            if (prevDelegate === this._mouseMoveDelegate) {
                return result;
            }

            $(document)
                .off('mousemove.' + this.widgetName)
                .off('mouseup.' + this.widgetName);

            $(document)
                .on('touchmove.' + this.widgetName, this._mouseMove)
                .on('touchend.' + this.widgetName, this._onTouchEnd)
                .on('tochleave.' + this.widgetName, this._onTouchEnd);

            return result;
        },

        /**
         * Documents' 'mousemove' event handler polyfill.
         *
         * @override
         * @param {Event} event - Touch event object.
         */
        _mouseMove: function (event) {
            event = this._touchToMouse(event);

            return this._super(event);
        },

        /**
         * Documents' 'touchend' event handler.
         */
        _onTouchEnd: function (event) {
            $(document).trigger('mouseup');

            return this._mouseUp(event);
        },

        /**
         * Removes previously assigned touch handlers.
         *
         * @override
         */
        _mouseUp: function () {
            this._removeTouchHandlers();

            return this._superApply(arguments);
        },

        /**
         * Removes previously assigned touch handlers.
         *
         * @override
         */
        _mouseDestroy: function () {
            this._removeTouchHandlers();

            return this._superApply(arguments);
        },

        /**
         * Removes touch events from document object.
         */
        _removeTouchHandlers: function () {
            $(document)
                .off('touchmove.' + this.widgetName)
                .off('touchend.' + this.widgetName)
                .off('touchleave.' + this.widgetName);
        },

        /**
         * Adds properties to the touch event to mimic mouse event.
         *
         * @param {Event} event - Touch event object.
         * @returns {Event}
         */
        _touchToMouse: function (event) {
            var orig = event.originalEvent,
                touch = orig.touches[0];

            return _.extend(event, {
                which:      1,
                pageX:      touch.pageX,
                pageY:      touch.pageY,
                clientX:    touch.clientX,
                clientY:    touch.clientY,
                screenX:    touch.screenX,
                screenY:    touch.screenY
            });
        }
    });

    return $.mage.touchSlider;
});
