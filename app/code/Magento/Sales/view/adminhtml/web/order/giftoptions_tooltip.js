/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'prototype'
], function () {
    'use strict';

    var GiftOptionsTooltip = Class.create();

    GiftOptionsTooltip.prototype = {
        _tooltipLines: [],
        _tooltipWindow: null,
        _tooltipWindowContent: null,
        _targetLinks: [],
        _eventMouseOver: null,
        _eventMouseOut: null,
        _styleOptions: null,
        _tooltipContentLoaderFunction: null,

        /**
         * Initialize tooltip object
         */
        initialize: function () {
            var options = Object.extend({
                'delta_x': 30,
                'delta_y': 0,
                zindex: 1000
            });

            this._styleOptions = options;
            this._eventMouseOver = this.showTooltip.bindAsEventListener(this);
            this._eventMouseOut = this.hideTooltip.bindAsEventListener(this);
        },

        /**
         * Set gift options tooltip window
         *
         * @param {String} windowId
         * @param {String} contentId
         *
         * @return boolean success
         */
        setTooltipWindow: function (windowId, contentId) {
            if (!$(windowId) || !$(contentId)) {
                return false;
            }
            this._tooltipWindow = $(windowId);
            this._tooltipWindowContent = $(contentId);
            $(document.body).insert({
                bottom: this._tooltipWindow
            });
            this.hideTooltip();

            return true;
        },

        /**
         * Add tooltip to specified link
         *
         * @param {String} linkId
         * @param {String} itemId - identifier of the item related to link
         *
         * @return boolean success
         */
        addTargetLink: function (linkId, itemId) {
            if ($(linkId)) {
                this._targetLinks[linkId] = [];
                this._targetLinks[linkId].object = $(linkId);
                this._targetLinks[linkId].itemId = itemId;
                this._registerEvents(this._targetLinks[linkId].object);

                return true;
            }

            return false;
        },

        /**
         * Detach event listeners from target links when tooltip is destroyed
         */
        destroy: function () {
            var linkId;

            for (linkId in this._targetLinks) { //eslint-disable-line guard-for-in
                Event.stopObserving(this._targetLinks[linkId].object, 'mouseover', this._eventMouseOver);
                Event.stopObserving(this._targetLinks[linkId].object, 'mouseout', this._eventMouseOut);
            }
        },

        /**
         *  Register event listeners
         *
         *  @param {HTMLElement} element
         */
        _registerEvents: function (element) {
            Event.observe(element, 'mouseover', this._eventMouseOver);
            Event.observe(element, 'mouseout', this._eventMouseOut);
        },

        /**
         * Move tooltip to mouse position
         *
         * @param {Prototype.Event} event
         */
        _moveTooltip: function (event) {
            var mouseX, mouseY;

            Event.stop(event);
            mouseX = Event.pointerX(event);
            mouseY = Event.pointerY(event);

            this.setStyles(mouseX, mouseY);
        },

        /**
         * Show tooltip
         *
         * @param {Object} event
         *
         * @return boolean success
         */
        showTooltip: function (event) {
            var link, itemId, tooltipContent;

            Event.stop(event);

            if (this._tooltipWindow) {
                link = Event.element(event);
                itemId = this._targetLinks[link.id].itemId;
                tooltipContent = '';

                if (Object.isFunction(this._tooltipContentLoaderFunction)) {
                    tooltipContent = this._tooltipContentLoaderFunction(itemId);
                }

                if (tooltipContent != '') { //eslint-disable-line eqeqeq
                    this._updateTooltipWindowContent(tooltipContent);
                    this._moveTooltip(event);
                    new Element.show(this._tooltipWindow);

                    return true;
                }
            }

            return false;
        },

        /**
         * Set tooltip window styles
         *
         * @param {Number} x
         * @param {Number} y
         */
        setStyles: function (x, y) {
            Element.setStyle(this._tooltipWindow, {
                position: 'absolute',
                top: y + this._styleOptions['delta_y'] + 'px',
                left: x + this._styleOptions['delta_x'] + 'px',
                zindex: this._styleOptions.zindex
            });
        },

        /**
         * Hide tooltip
         */
        hideTooltip: function () {
            if (this._tooltipWindow) {
                new Element.hide(this._tooltipWindow);
            }
        },

        /**
         * Set gift options tooltip content loader function
         * This function should accept at least one parameter that will serve as an item ID
         *
         * @param {Function} loaderFunction - loader function
         */
        setTooltipContentLoaderFunction: function (loaderFunction) {
            this._tooltipContentLoaderFunction = loaderFunction;
        },

        /**
         * Update tooltip window content
         *
         * @param {String} content
         */
        _updateTooltipWindowContent: function (content) {
            this._tooltipWindowContent.update(content);
        }
    };

    window.giftOptionsTooltip = new GiftOptionsTooltip();
});
