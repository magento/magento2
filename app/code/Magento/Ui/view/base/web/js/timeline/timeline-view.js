/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'Magento_Ui/js/lib/view/utils/raf',
    'uiRegistry',
    'uiClass'
], function (ko, $, _, raf, registry, Class) {
    'use strict';

    var hasClassList = (function () {
        var list = document.createElement('_').classList;

        return !!list && !list.toggle('_test', false);
    })();

    /**
     * Polyfill of the 'classList.toggle' method.
     *
     * @param {HTMLElement} elem
     */
    function toggleClass(elem) {
        var classList   = elem.classList,
            args        = Array.prototype.slice.call(arguments, 1),
            $elem;

        if (hasClassList) {
            classList.toggle.apply(classList, args);
        } else {
            $elem = $(elem);
            $elem.toggleClass.apply($elem, args);
        }
    }

    return Class.extend({
        defaults: {
            selectors: {
                content: '.timeline-content',
                timeUnit: '.timeline-unit',
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
                'refresh',
                'initContent',
                'initItem',
                'initTimeUnit',
                'getItemBindings',
                'updateItemsPosition',
                'onScaleChange',
                'onEventElementRender',
                'onWindowResize',
                'onContentScroll',
                'onDataReloaded',
                'onToStartClick',
                'onToEndClick'
            );

            this._super()
                .initModel()
                .waitContent();

            return this;
        },

        /**
         * Applies listeners for the model properties changes.
         *
         * @returns {TimelineView} Chainable.
         */
        initModel: function () {
            var model = registry.get(this.model);

            model.on('scale', this.onScaleChange);
            model.source.on('reloaded', this.onDataReloaded);

            this.model = model;

            return this;
        },

        /**
         * Applies DOM watcher for the
         * content element rendering.
         *
         * @returns {TimelineView} Chainable.
         */
        waitContent: function () {
            $.async({
                selector: this.selectors.content,
                component: this.model
            }, this.initContent);

            return this;
        },

        /**
         * Initializes timelines' content element.
         *
         * @param {HTMLElement} content
         * @returns {TimelineView} Chainable.
         */
        initContent: function (content) {
            this.$content = content;

            $(content).on('scroll', this.onContentScroll);
            $(window).on('resize', this.onWindowResize);

            $.async(this.selectors.item, content, this.initItem);
            $.async(this.selectors.event, content, this.onEventElementRender);
            $.async(this.selectors.timeUnit, content, this.initTimeUnit);

            this.refresh();

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
         * Initializes timeline unit element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView} Chainable.
         */
        initTimeUnit: function (elem) {
            $(elem).bindings(this.getTimeUnitBindings());

            return this;
        },

        /**
         * Updates items positions in a
         * loop if state of a view has changed.
         */
        refresh: function () {
            raf(this.refresh);

            if (this._update) {
                this._update = false;

                this.updateItemsPosition();
            }
        },

        /**
         * Returns object width additional bindings
         * for a timeline unit element.
         *
         * @returns {Object}
         */
        getTimeUnitBindings: function () {
            return {
                style: {
                    width: ko.computed(function () {
                        return this.getTimeUnitWidth() + '%';
                    }.bind(this))
                }
            };
        },

        /**
         * Returns object with additional
         * bindings for a timeline item element.
         *
         * @param {Object} ctx
         * @returns {Object}
         */
        getItemBindings: function (ctx) {
            return {
                style: {
                    width: ko.computed(function () {
                        return this.getItemWidth(ctx.$row()) + '%';
                    }.bind(this)),

                    'margin-left': ko.computed(function () {
                        return this.getItemMargin(ctx.$row()) + '%';
                    }.bind(this))
                }
            };
        },

        /**
         * Calculates width in percents of a timeline unit element.
         *
         * @returns {Number}
         */
        getTimeUnitWidth: function () {
            return 100 / this.model.scale;
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

            return this.getTimeUnitWidth()  * days;
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

            return this.getTimeUnitWidth() * offset;
        },

        /**
         * Returns collection of currently available
         * timeline item elements.
         *
         * @returns {Array<HTMLElement>}
         */
        getItems: function () {
            var items = this.$content.querySelectorAll(this.selectors.item);

            return _.toArray(items);
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
         * @param {HTMLElement} $elem
         * @returns {TimelineView} Chainable.
         */
        updatePositionFor: function ($elem) {
            var $event      = $elem.querySelector(this.selectors.event),
                leftEdge    = this.getLeftEdgeFor($elem),
                rightEdge   = this.getRightEdgeFor($elem);

            if ($event) {
                $event.style.left = Math.max(-leftEdge, 0) + 'px';
                $event.style.right = Math.max(rightEdge, 0) + 'px';
            }

            toggleClass($elem, '_scroll-start', leftEdge < 0);
            toggleClass($elem, '_scroll-end', rightEdge > 0);

            return this;
        },

        /**
         * Scrolls content area to the start of provided element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView}
         */
        toStartOf: function (elem) {
            var leftEdge = this.getLeftEdgeFor(elem);

            this.$content.scrollLeft += leftEdge;

            return this;
        },

        /**
         * Scrolls content area to the end of provided element.
         *
         * @param {HTMLElement} elem
         * @returns {TimelineView}
         */
        toEndOf: function (elem) {
            var rightEdge = this.getRightEdgeFor(elem);

            this.$content.scrollLeft += rightEdge + 1;

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
            var leftOffset = elem.getBoundingClientRect().left;

            return leftOffset - this.$content.getBoundingClientRect().left;
        },

        /**
         * Calculates location of the right edge of an element
         * relative to the contents' right edge.
         *
         * @param {HTMLElement} elem
         * @returns {Number}
         */
        getRightEdgeFor: function (elem) {
            var elemWidth   = elem.offsetWidth,
                leftEdge    = this.getLeftEdgeFor(elem);

            return leftEdge + elemWidth - this.$content.offsetWidth;
        },

        /**
         * 'To Start' button 'click' event handler.
         *
         * @param {jQueryEvent} event
         */
        onToStartClick: function (event) {
            var elem = event.originalEvent.currentTarget;

            event.stopPropagation();

            this.toStartOf(elem);
        },

        /**
         * 'To End' button 'click' event handler.
         *
         * @param {jQueryEvent} event
         */
        onToEndClick: function (event) {
            var elem = event.originalEvent.currentTarget;

            event.stopPropagation();

            this.toEndOf(elem);
        },

        /**
         * Handler of the scale value 'change' event.
         */
        onScaleChange: function () {
            this._update = true;
        },

        /**
         * Callback function which is invoked
         * when event element was rendered.
         */
        onEventElementRender: function () {
            this._update = true;
        },

        /**
         * Window 'resize' event handler.
         */
        onWindowResize: function () {
            this._update = true;
        },

        /**
         * Content container 'scroll' event handler.
         */
        onContentScroll: function () {
            this._update = true;
        },

        /**
         * Data 'reload' event handler.
         */
        onDataReloaded: function () {
            this._update = true;
        }
    });
});
