/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/lib/ko/extender/bound-nodes',
    'uiComponent'
], function ($, ko, _, utils, registry, boundedNodes, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            rootSelector: '${ $.columnsProvider }:.admin__data-grid-wrap',
            tableSelector: '${ $.rootSelector } -> table.data-grid',
            columnSelector: '${ $.tableSelector } thead tr th',
            fieldSelector: '${ $.tableSelector } tbody tr td',

            imports: {
                storageColumnsData: '${ $.storageConfig.path }.storageColumnsData'
            },
            storageColumnsData: {},
            columnsElements: {},
            showLines: 4,
            resizableElementClass: 'shadow-div',
            resizingColumnClass: '_resizing',
            inResizeClass: '_in-resize',
            visibleClass: '_resize-visible',
            resizable: false,
            resizeConfig: {
                minColumnWidth: 40,
                maxRowsHeight: [],
                curResizeElem: {},
                depResizeElem: {},
                previousWidth: null
            }
        },

        /**
         * Initialize application -
         * binding functions context,
         * set handlers for table elements
         *
         * @returns {Object} Chainable
         */
        initialize: function () {
            _.bindAll(
                this,
                'initTable',
                'initColumn',
                'mousedownHandler',
                'mousemoveHandler',
                'mouseupHandler',
                'refreshLastColumn',
                'refreshMaxRowHeight',
                '_eventProxy'
            );

            this._super();
            this.observe(['maxRowsHeight']);
            this.maxRowsHeight([]);

            $.async(this.tableSelector, this.initTable);
            $.async(this.columnSelector, this.initColumn);
            $.async(this.fieldSelector, this.refreshMaxRowHeight);

            return this;
        },

        /**
         * Set table element and adds handler to mousedown on headers
         *
         * @returns {Object} Chainable
         */
        initTable: function (table) {
            this.table = table;

            return this;
        },

        /**
         * Init columns elements,
         * set width to current column element,
         * add resizable element to columns header,
         * check and add no-resize class to last column,
         * stop parents events,
         * add handler to visibility column
         *
         * @param {Object} column - columns header element (th)
         */
        initColumn: function (column) {
            var model = ko.dataFor(column);

            model.width = this.getDefaultWidth(column);

            if (!this.hasColumn(model)) {
                this.initResizableElement(column);
                this.columnsElements[model.index] = column;
                $(column).outerWidth(model.width);
                this.setStopPropagationHandler(column);
            }

            this.refreshLastColumn(column);

            model.on('visible', this.refreshLastColumn.bind(this, column));
        },

        /**
         * Check element is resizable or not
         * and append resizable element to DOM
         *
         * @param {Object} column - columns header element (th)
         * @returns {Boolean}
         */
        initResizableElement: function (column) {
            var model = ko.dataFor(column),
                ctx = ko.contextFor(column),
                tempalteDragElement = '<div class="' + ctx.$parent.resizeConfig.classResize + '"></div>';

            if (_.isUndefined(model.resizeEnabled) || model.resizeEnabled) {
                $(column).append(tempalteDragElement);

                return true;
            }

            return false;
        },

        /**
         * Check event target and if need stop parents event,
         *
         * @param {Object} column - columns header element (th)
         * @returns {Boolean}
         */
        setStopPropagationHandler: function (column) {
            var events,
                click,
                mousedown;

            $(column).on('click', this._eventProxy);
            $(column).on('mousedown', this._eventProxy);

            events = $._data(column, 'events');

            click = events.click;
            mousedown = events.mousedown;
            click.unshift(click.pop());
            mousedown.unshift(mousedown.pop());

            return this;
        },

        /**
         * Check event target and stop event if need
         *
         * @param {Object} event
         */
        _eventProxy: function (event) {
            if ($(event.target).is('.' + this.resizableElementClass)) {

                if (event.type === 'click') {
                    event.stopImmediatePropagation();
                } else if (event.type === 'mousedown') {
                    this.mousedownHandler(event);
                }
            }
        },

        /**
         * Check visible columns and set disable class to resizable elements,
         *
         * @param {Object} column - columns header element (th)
         */
        refreshLastColumn: function (column) {
            var i = 0,
                columns = $(column).parent().children().not(':hidden'),
                length = columns.length;

            $('.' + this.visibleClass).removeClass(this.visibleClass);

            $(column).parent().children().not(':hidden').last().addClass(this.visibleClass);

            for (i; i < length; i++) {

                if (!columns.eq(i).find('.' + this.resizableElementClass).length && i) {
                    columns.eq(i - 1).addClass(this.visibleClass);
                }
            }

        },

        /**
         * Refresh max height to row elements,
         *
         * @param {Object} elem - (td)
         */
        refreshMaxRowHeight: function (elem) {
            var rowsH = this.maxRowsHeight(),
                curEL = $(elem).find('div'),
                height,
                obj = this.hasRow($(elem).parent()[0], true);

            curEL.css('white-space', 'nowrap');
            height = curEL.height() * this.showLines;
            curEL.css('white-space', 'normal');

            if (obj) {
                if (obj.maxHeight < height) {
                    rowsH[_.indexOf(rowsH, obj)].maxHeight = height;
                } else {
                    return false;
                }
            } else {
                rowsH.push({
                    elem: $(elem).parent()[0],
                    maxHeight: height
                });
            }

            $(elem).parent().children().find('div._hideOverflow').css('max-height', height + 'px');
            this.maxRowsHeight(rowsH);
        },

        /**
         * Set resize class to elements when resizable
         */
        _setResizeClass: function () {
            var rowElements = $(this.table).find('tr');

            rowElements
                .find('td:eq(' + this.resizeConfig.curResizeElem.ctx.$index() + ')')
                .addClass(this.resizingColumnClass);
            rowElements
                .find('td:eq(' + this.resizeConfig.depResizeElem.ctx.$index() + ')')
                .addClass(this.resizingColumnClass);
        },

        /**
         * Remove resize class to elements when resizable
         */
        _removeResizeClass: function () {
            var rowElements = $(this.table).find('tr');

            rowElements
                .find('td:eq(' + this.resizeConfig.curResizeElem.ctx.$index() + ')')
                .removeClass(this.resizingColumnClass);
            rowElements
                .find('td:eq(' + this.resizeConfig.depResizeElem.ctx.$index() + ')')
                .removeClass(this.resizingColumnClass);
        },

        /**
         * Check conditions to resize
         *
         * @returns {Boolean}
         */
        _canResize: function (column) {
            if (
                $(column).hasClass(this.visibleClass) ||
                !$(this.resizeConfig.depResizeElem.elem).find('.' + this.resizableElementClass).length
            ) {
                return false;
            }

            return true;
        },

        /**
         * Mouse down event handler,
         * find current and dep column to resize
         *
         * @param {Object} event
         */
        mousedownHandler: function (event) {
            var target = event.target,
                column = $(target).parent()[0],
                cfg = this.resizeConfig,
                body = $('body');

            event.stopImmediatePropagation();
            cfg.curResizeElem.model = ko.dataFor(column);
            cfg.curResizeElem.ctx = ko.contextFor(column);
            cfg.curResizeElem.elem = this.hasColumn(cfg.curResizeElem.model, true);
            cfg.curResizeElem.position = event.pageX;
            cfg.depResizeElem.elem = this.getNextElement(cfg.curResizeElem.elem);
            cfg.depResizeElem.model = ko.dataFor(cfg.depResizeElem.elem);
            cfg.depResizeElem.ctx = ko.contextFor(cfg.depResizeElem.elem);

            this._setResizeClass();

            if (!this._canResize(column)) {
                return false;
            }

            event.stopPropagation();
            this.resizable = true;
            cfg.curResizeElem.model.width = $(cfg.curResizeElem.elem).outerWidth();
            cfg.depResizeElem.model.width = $(cfg.depResizeElem.elem).outerWidth();
            body.addClass(this.inResizeClass);
            body.bind('mousemove', this.mousemoveHandler);
            $(window).bind('mouseup', this.mouseupHandler);
        },

        /**
         * Mouse move event handler,
         * change columns width
         *
         * @param {Object} event
         */
        mousemoveHandler: function (event) {
            var cfg = this.resizeConfig,
                width = event.pageX - cfg.curResizeElem.position;

            event.stopPropagation();
            event.preventDefault();

            if (
                this.resizable &&
                cfg.minColumnWidth < cfg.curResizeElem.model.width + width &&
                cfg.minColumnWidth < cfg.depResizeElem.model.width - width
            ) {
                if (cfg.previousWidth !== width) {
                    cfg.curResizeElem.model.width += width;
                    cfg.depResizeElem.model.width -= width;
                    $(cfg.curResizeElem.elem).outerWidth(cfg.curResizeElem.model.width);
                    $(cfg.depResizeElem.elem).outerWidth(cfg.depResizeElem.model.width);
                    cfg.previousWidth = width;
                    cfg.curResizeElem.position = event.pageX;
                }
            } else if (width <= -(cfg.curResizeElem.model.width - cfg.minColumnWidth)) {
                $(cfg.curResizeElem.elem).outerWidth(cfg.minColumnWidth);

                $(cfg.depResizeElem.elem).outerWidth(
                    cfg.depResizeElem.model.width +
                    cfg.curResizeElem.model.width -
                    cfg.minColumnWidth
                );
            } else if (width >= cfg.depResizeElem.model.width - cfg.minColumnWidth) {

                $(cfg.depResizeElem.elem).outerWidth(cfg.minColumnWidth);
                $(cfg.curResizeElem.elem).outerWidth(
                    cfg.curResizeElem.model.width +
                    cfg.depResizeElem.model.width -
                    cfg.minColumnWidth
                );
            }
        },

        /**
         * Mouse up event handler,
         * change columns width
         *
         * @param {Object} event
         */
        mouseupHandler: function (event) {
            var cfg = this.resizeConfig,
                body = $('body');

            event.stopPropagation();
            event.preventDefault();

            this._removeResizeClass();
            this.storageColumnsData[cfg.curResizeElem.model.index] = cfg.curResizeElem.model.width;
            this.storageColumnsData[cfg.depResizeElem.model.index] = cfg.depResizeElem.model.width;
            this.resizable = false;

            this.store('storageColumnsData');

            body.removeClass(this.inResizeClass);
            body.unbind('mousemove', this.mousemoveHandler);
            $(window).unbind('mouseup', this.mouseupHandler);
        },

        /**
         * Find dependency element
         *
         * @param {Object} element - current element
         * @returns {Object} next element data
         */
        getNextElement: function (element) {
            var nextElem = $(element).next()[0],
                nextElemModel = ko.dataFor(nextElem),
                nextElemData = this.hasColumn(nextElemModel, true);

            if (nextElemData) {

                if (nextElemModel.visible()) {
                    return nextElemData;
                }

                return this.getNextElement(nextElem);
            }
        },

        /**
         * Get default width
         *
         * @param {Object} column - (th) element
         * @return {String} width for current column
         */
        getDefaultWidth: function (column) {
            var model = ko.dataFor(column);

            if (this.storageColumnsData[model.index]) {
                return this.storageColumnsData[model.index];
            }

            if (model.resizeDefaultWidth) {
                return parseInt(model.resizeDefaultWidth, 10);
            }

            return 'auto';
        },

        /**
         * Check column is render or not
         *
         * @param {Object} model - cur column model
         * @param {Boolean} returned - need return column object or not
         * @return {Boolean} if returned param is false, returned boolean falue, else return current object data
         */
        hasColumn: function (model, returned) {
            if (this.columnsElements.hasOwnProperty(model.index)) {

                if (returned) {
                    return this.columnsElements[model.index];
                }

                return true;
            }

            return false;
        },

        /**
         * Check row is render or not
         *
         * @param {Object} elem - cur column element
         * @param {Boolean} returned - need return column object or not
         * @return {Boolean} if returned param is false, returned boolean falue, else return current object data
         */
        hasRow: function (elem, returned) {
            var i = 0,
                el = this.maxRowsHeight(),
                length = el.length;

            for (i; i < length; i++) {

                if (this.maxRowsHeight()[i].elem === elem) {

                    if (returned) {
                        return this.maxRowsHeight()[i];
                    }

                    return true;
                }
            }

            return false;
        }
    });
});
