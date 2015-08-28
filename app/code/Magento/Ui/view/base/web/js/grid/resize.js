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
         * Initialize application
         */
        initialize: function () {
            _.bindAll(
                this,
                'initTable',
                'initColumn',
                'initTd',
                'mousedownHandler',
                'mousemoveHandler',
                'mouseupHandler',
                'stopEventPropagation',
                'refreshLastColumn'
            );

            this._super();
            this.observe(['maxRowsHeight']);
            this.maxRowsHeight([]);

            $.async(this.tableSelector, this.initTable);
            $.async(this.columnSelector, this.initColumn);
            $.async(this.fieldSelector, this.initTd);

            return this;
        },

        initTable: function (table) {
            this.table = table;
            $(table).on('mousedown', 'thead tr th .' + this.resizableElementClass, this.mousedownHandler);
        },

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

        initTd: function (td) {
            this.refreshMaxRowHeight(td);
        },

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

        setStopPropagationHandler: function (column) {
            var events;

            $(column).on('click', this.stopEventPropagation);
            events = $._data(column, 'events').click;
            events.unshift(events.pop());
        },

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

        mousedownHandler: function (event) {
            var target = event.target,
                cfg = this.resizeConfig,
                body = $('body');

            cfg.curResizeElem.model = ko.dataFor($(target).parent()[0]);
            cfg.curResizeElem.ctx = ko.contextFor($(target).parent()[0]);
            cfg.curResizeElem.elem = this.hasColumn(cfg.curResizeElem.model, true);
            cfg.curResizeElem.position = event.pageX;
            cfg.depResizeElem.elem = this.getNextElement(cfg.curResizeElem.elem);
            cfg.depResizeElem.model = ko.dataFor(cfg.depResizeElem.elem);
            cfg.depResizeElem.ctx = ko.contextFor(cfg.depResizeElem.elem);

            $(this.table).find('tr')
                .find('td:eq(' + cfg.curResizeElem.ctx.$index() + ')')
                .addClass(this.resizingColumnClass);
            $(this.table).find('tr')
                .find('td:eq(' + cfg.depResizeElem.ctx.$index() + ')')
                .addClass(this.resizingColumnClass);

            if (
                $(event.target).parent().hasClass(this.visibleClass) ||
                !$(cfg.depResizeElem.elem).find('.' + this.resizableElementClass).length
            ) {
                return false;
            }

            event.stopPropagation();
            this.resizable = true;

            cfg.curResizeElem.model.width === 'auto' ?
                cfg.curResizeElem.model.width = $(cfg.curResizeElem.elem).outerWidth() : false;
            cfg.depResizeElem.model.width === 'auto' ?
                cfg.depResizeElem.model.width = $(cfg.depResizeElem.elem).outerWidth() : false;

            body.addClass(this.inResizeClass);
            body.bind('mousemove', this.mousemoveHandler);
            $(window).bind('mouseup', this.mouseupHandler);
        },

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
        * Mouse up handler
        * @param {Object} event
        */
        mouseupHandler: function (event) {
            var cfg = this.resizeConfig,
                body = $('body');

            event.stopPropagation();
            event.preventDefault();

            $(this.table).find('tr')
                .find('td:eq(' + cfg.curResizeElem.ctx.$index() + ')')
                .removeClass(this.resizingColumnClass);
            $(this.table).find('tr')
                .find('td:eq(' + cfg.depResizeElem.ctx.$index() + ')')
                .removeClass(this.resizingColumnClass);

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
         * @param {Number} index - current element index
         * @param {Boolean} typeObject - type returned value (Object or string)
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

        getColumnWidth: function (column) {
            if (this.hasColumn(column)) {
                return this.hasColumn(column, true).width();
            }

            return 'auto';
        },

        getDefaultWidth: function (column) {
            var model = ko.dataFor(column);

            if (this.storageColumnsData[model.index]) {
                return this.storageColumnsData[model.index];
            }

            if (model.resizeDefaultWidth) {
                return parseInt(model.resizeDefaultWidth);
            }

            return 'auto';
        },

        hasColumn: function (model, returned) {
            if (this.columnsElements.hasOwnProperty(model.index)) {

                if (returned) {
                    return this.columnsElements[model.index];
                }

                return true;
            }

            return false;
        },

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
        },

        stopEventPropagation: function (e) {
            if ($(e.target).is('.' + this.resizableElementClass)) {
                e.stopImmediatePropagation();
            }
        }
    });
});
