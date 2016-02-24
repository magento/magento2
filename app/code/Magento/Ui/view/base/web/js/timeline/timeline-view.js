/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiRegistry',
    'uiClass'
], function (ko, $, _, registry, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            selectors: {
                content: '.timeline-content',
                item: '.timeline-item:not([data-role=no-data-msg])',
                event: '.timeline-event'
            }
        },

        /**
         * Initializes TimelineView component.
         *
         * @returns {TimelineView} Chainable.
         */
        initialize: function () {
            _.bindAll(
                this,
                'initContent',
                'initItem',
                'getItemBindings',
                'onEventElementRender',
                'onWindowResize',
                'onContentScroll',
                'onDataReloaded',
                'onToStartClick',
                'onToEndClick'
            );

            this._super();

            this.model = registry.get(this.model);

            this.model.source.on('reloaded', this.onDataReloaded);

            $.async({
                selector: this.selectors.content,
                component: this.model
            }, this.initContent);

            $(window).on('resize', this.onWindowResize);

            return this;
        },

        /**
         * Initializes timelines' content container element.
         *
         * @param {HTMLElement} content
         * @returns {TimelineView} Chainable.
         */
        initContent: function (content) {
            this.$content = $(content);

            this.$content.on('scroll', this.onContentScroll);

            $.async(this.selectors.item, content, this.initItem);
            $.async(this.selectors.event, content, this.onEventElementRender);

            return this;
        },

        /**
         * Initializes timeline item element,
         * e.g. establishes event listeners and applies data bindings.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView} Chainable.
         */
        initItem: function (elem) {
            $(elem)
                .bindings(this.getItemBindings)
                .on('click', '._toend', this.onToEndClick)
                .on('click', '._tostart', this.onToStartClick);

            return this;
        },

        /**
         * Returns object with additional
         * bindings for timeline item.
         *
         * @param {Object} ctx
         * @returns {Object}
         */
        getItemBindings: function (ctx) {
            return {
                style: {
                    width: ko.computed(function () {
                        var record = ctx.$row();

                        return this.getItemWidth(record);
                    }.bind(this)),

                    'margin-left': ko.computed(function () {
                        var record = ctx.$row();

                        return this.getItemMargin(record);
                    }.bind(this))
                }
            };
        },

        /**
         * Calculates width of a record in percents.
         *
         * @param {Object} record
         * @returns {String}
         */
        getItemWidth: function (record) {
            var days = 0;

            if (record) {
                days = this.model.getDaysLength(record);
            }

            return 100 / 7 * days + '%';
        },

        /**
         * Calculates left margin value for provided record.
         *
         * @param {Object} record
         * @returns {String}
         */
        getItemMargin: function (record) {
            var offset = 0;

            if (record) {
                offset = this.model.getStartDelta(record);
            }

            return 100 / 7 * offset + '%';
        },

        /**
         * Returns collection of currently available
         * timeline item elements.
         *
         * @returns {Array<HTMLElement>}
         */
        getItems: function () {
            return $(this.selectors.item, this.$content).toArray();
        },

        /**
         * Updates positions of timeline elements.
         *
         * @returns {TimelineView} Chainable.
         */
        updateItemsPosition: function () {
            this.getItems()
                .forEach(this.updatePositionFor, this);

            return this;
        },

        /**
         * Updates position of provided timeline element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView} Chainable.
         */
        updatePositionFor: function (elem) {
            var $elem       = $(elem),
                $event      = $(this.selectors.event, $elem),
                leftEdge    = this.getLeftEdgeFor(elem),
                rightEdge   = this.getRightEdgeFor(elem);

            $event.css({
                left: leftEdge < 0 ? -leftEdge : 0,
                right: rightEdge > 0 ? rightEdge : 0
            });

            $elem.toggleClass('_scroll-start', leftEdge < 0)
                .toggleClass('_scroll-end', rightEdge > 0);

            return this;
        },

        /**
         * Scrolls content area to the start of provided element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView}
         */
        toStartOf: function (elem) {
            var leftEdge    = this.getLeftEdgeFor(elem),
                scroll      = this.$content.scrollLeft();

            this.$content.scrollLeft(scroll + leftEdge);

            return this;
        },

        /**
         * Scrolls content area to the end of provided element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView}
         */
        toEndOf: function (elem) {
            var rightEdge   = this.getRightEdgeFor(elem),
                scroll      = this.$content.scrollLeft();

            this.$content.scrollLeft(scroll + rightEdge + 1);

            return this;
        },

        /**
         * Calculates location of the left edge of an element
         * relative to the contents' left edge.
         *
         * @param {HTMLElement} elem
         * @returns {Number}
         */
        getLeftEdgeFor: function (elem) {
            var leftOffset = $(elem).offset().left;

            return leftOffset - this.$content.offset().left;
        },

        /**
         * Calculates location of the right edge of an element
         * relative to the contents' right edge.
         *
         * @param {HTMLElement} elem
         * @returns {Number}
         */
        getRightEdgeFor: function (elem) {
            var elemWidth   = $(elem).width(),
                leftEdge    = this.getLeftEdgeFor(elem);

            return leftEdge + elemWidth - this.$content.width();
        },

        /**
         * 'To Start' button 'click' event handler.
         *
         * @param {jQueryEvent} event
         */
        onToStartClick: function (event) {
            var elem = event.originalEvent.currentTarget;

            this.toStartOf(elem);
        },

        /**
         * 'To End' button 'click' event handler.
         *
         * @param {jQueryEvent} event
         */
        onToEndClick: function (event) {
            var elem = event.originalEvent.currentTarget;

            this.toEndOf(elem);
        },

        /**
         * Callback function which is invoked
         * when event element was rendered.
         */
        onEventElementRender: function () {
            this.updateItemsPosition();
        },

        /**
         * Window 'resize' event handler.
         */
        onWindowResize: function () {
            this.updateItemsPosition();
        },

        /**
         * Content container 'scroll' event handler.
         */
        onContentScroll: function () {
            this.updateItemsPosition();
        },

        /**
         * Data 'reload' event handler.
         */
        onDataReloaded: function () {
            this.updateItemsPosition();
        }
    });
});
