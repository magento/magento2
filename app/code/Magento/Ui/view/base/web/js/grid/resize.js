/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'mage/utils/dom-observer',
    'Magento_Ui/js/lib/ko/extender/bound-nodes',
    'uiComponent'
], function ($, ko, _, utils, registry, domObserver, boundedNodes, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            imports: {
                storageColumnsData: '${ $.storageConfig.path }.storageColumnsData'
            },
            listens: {
                '${ $.provider }:params.sorting.field': 'onSortChange'
            },
            modules: {
                source: '${ $.provider }'
            },
            storageColumnsData: {},
            columnsElements: {},
            tableWidth: null,

            resizeConfig: {
                nameSpacing: {
                    cellsDataAttribute: 'data-cl-resize',
                    cellsDataAttrPrefix: 'column-',
                    divsResizableAttribute: 'data-cl-elem',
                    divsResizableAttrName: 'shadow-div'
                },
                dataColumnName: 'data-cl-name',
                showLines: 4,
                minColumnWidth: 40,
                resizable: false,
                maxRowsHeight: [],
                curResizeElem: {},
                depResizeElem: {},
                previousWidth: null,
                columnsArray: [],
                visibleClass: '_resize-visible'
            }
        },

        /**
         * Initialize application
         */
        initialize: function () {
            _.bindAll(
                this,
                'initRoot',
                'initTable',
                'initColumn',
                'initTd',
                'mousedownHandler',
                'mousemoveHandler',
                'mouseupHandler',
                'click'
            );

            this._super();
            this.observe(['maxRowsHeight']);
            this.maxRowsHeight([]);

            registry.get(this.columnsProvider, function (listing) {
                boundedNodes.get(listing, this.initRoot);
            }.bind(this));

            return this;
        },

        initRoot: function (root) {
            if ($(root).is('.admin__data-grid-wrap')) {
                domObserver.get('table', this.initTable, root);
            }
        },

        initTable: function (table) {
            this.table = table;
            this.tableWidth = $(table).outerWidth();
            $(table).on('mousedown', 'thead tr th .shadow-div', this.mousedownHandler);
            domObserver.get('thead tr th', this.initColumn, table);
            domObserver.get('tbody tr td', this.initTd, table);
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

        getDefaultWidth: function (column) {
            var model = ko.dataFor(column);

            boundedNodes.get(model);

            if (this.storageColumnsData[model.index]) {
                return this.storageColumnsData[model.index];
            }

            if (model.resizeDefaultWidth) {
                return parseInt(model.resizeDefaultWidth);
            }

            return 'auto';
        },

        initColumn: function (column) {
            var model = ko.dataFor(column),
                events;

            model.width = this.getDefaultWidth(column);

            if (!this.hasColumn(model)) {
                this.initResizableElement(column);
                this.columnsElements[model.index] = column;
                $(column).outerWidth(model.width);

                $(column).on('click', this.click);
                events = $._data(column, 'events').click;
                events.unshift(events.pop());
            }
            this.refreshLastColumn(column);
            model.on('visible', this.refreshVisibility.bind(this, model, column));
        },

        click: function (e) {
            if ($(e.target).is('.shadow-div')) {
                e.stopImmediatePropagation();
            }
        },

        initTd: function (td) {
            this.refreshMaxRowHeight(td);
        },

        refreshVisibility: function (model, column) {
            this.refreshLastColumn(column);
        },

        refreshLastColumn: function (column) {
            var i = 0,
                columns = $(column).parent().children().not(':hidden'),
                length = columns.length;

            $('.' + this.resizeConfig.visibleClass).removeClass(this.resizeConfig.visibleClass);

            $(column).parent().children().not(':hidden').last().addClass(this.resizeConfig.visibleClass);

            for (i; i < length; i++) {

                if (!columns.eq(i).find('.' + this.resizeConfig.nameSpacing.divsResizableAttrName).length && i) {
                    columns.eq(i-1).addClass(this.resizeConfig.visibleClass);
                }
            }

        },

        mousedownHandler: function (event) {
            var target = event.target,
                cfg = this.resizeConfig,
                body = $('body');

            cfg.curResizeElem.model = ko.dataFor($(target).parent()[0]);
            cfg.curResizeElem.elem = this.hasColumn(cfg.curResizeElem.model, true);
            cfg.curResizeElem.position = event.pageX;
            cfg.depResizeElem.elem = this.getNextElement(cfg.curResizeElem.elem);
            cfg.depResizeElem.model = ko.dataFor(cfg.depResizeElem.elem);

            if (
                $(event.target).parent().hasClass(this.resizeConfig.visibleClass) ||
                !$(cfg.depResizeElem.elem).find('.' + this.resizeConfig.nameSpacing.divsResizableAttrName).length
            ) {
                return false;
            }

            event.stopPropagation();
            cfg.resizable = true;

            cfg.curResizeElem.model.width === 'auto' ?
                cfg.curResizeElem.model.width = $(cfg.curResizeElem.elem).outerWidth() : false;
            cfg.depResizeElem.model.width === 'auto' ?
                cfg.depResizeElem.model.width = $(cfg.depResizeElem.elem).outerWidth() : false;

            body.addClass('_in-resize');
            body.bind('mousemove', this.mousemoveHandler);
            $(window).bind('mouseup', this.mouseupHandler);
        },

        mousemoveHandler: function (event) {
            var cfg = this.resizeConfig,
                width = event.pageX - cfg.curResizeElem.position;

            event.stopPropagation();
            event.preventDefault();

            if (
                cfg.resizable &&
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

            this.storageColumnsData[cfg.curResizeElem.model.index] = cfg.curResizeElem.model.width;
            this.storageColumnsData[cfg.depResizeElem.model.index] = cfg.depResizeElem.model.width;
            cfg.resizable = false;

            this.store('storageColumnsData');

            body.removeClass('_in-resize');
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

        refreshMaxRowHeight: function (elem) {
            var rowsH = this.maxRowsHeight(),
                curEL = $(elem).find('div'),
                height,
                obj = this.hasRow($(elem).parent()[0], true);

            curEL.css('white-space', 'nowrap');
            height = curEL.height() * 4;
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

        onSortChange: function () {
            this.maxRowsHeight([]);
        }
    });
});
