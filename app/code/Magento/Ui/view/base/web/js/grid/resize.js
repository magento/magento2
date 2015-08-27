/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'uiComponent'
], function ($, ko, _, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {

            links: {
                visible: '${ $.storageConfig.path }.visible',
                sorting: '${ $.storageConfig.path }.sorting'
            },
            imports: {
                exportSorting: 'sorting'
            },
            listens: {
                '${ $.provider }:params.sorting.field': 'onSortChange'
            },
            modules: {
                source: '${ $.provider }'
            },
            resizeConfig: {
                entryTable: {
                    state: false,
                    promise: $.Deferred()
                },
                entryConfig: {
                    state: false,
                    promise: $.Deferred()
                },
                nameSpacing: {
                    cellsDataAttribute: 'data-cl-resize',
                    cellsDataAttrPrefix: 'column-',
                    divsResizableAttribute: 'data-cl-elem',
                    divsResizableAttrName: 'shadow-div'
                },
                columnsVisibility: ['true'],
                showLines: 4,
                minColumnWidth: 40,
                dragRange: 10,
                resizable: false
            }
        },

        /**
         * Initialize application
         */
        initialize: function () {
            _.bindAll(this, 'refreshResizeConfig', 'initResizeConfig', 'entryInTable');
            this._super();
        },

        /**
         * Initialize eventListeners
         */
        eventListener: function () {
            var self = this,
                cfg = this.resizeConfig;

            $(this.table).bind('mouseenter', this.entryInTable);
            cfg.entryTable.promise.done(function () {
                self.initResizeConfig();
                self.resizable();
            });
        },

        /**
         * @see resolve promise when mouse first time entry in the table
         * @event mouse enter
         * @returns {Object} with entry property
         */
        entryInTable: function () {
            var cfg = this.resizeConfig;

            if (!cfg.entryTable.state) {
                cfg.entryTable.state = true;
                cfg.entryTable.promise.resolve();
            }

            return cfg.entryTable;
        },

        /**
         * Initialize resize handlers
         */
        resizable: function () {
            var self = this,
                cfg = this.resizeConfig,
                cur = {
                    element: null,
                    id: null,
                    position: null
                },
                divAttrProp = Object.getOwnPropertyNames(this.divsAttrParams)[0],
                divAttrPropValue = this.divsAttrParams[divAttrProp],
                dep,
                returnObject = true,
                lastWidth,

                /**
                 * Mouse move handler
                 * @param {Object} event
                 */
                resizeMousemove = function (event) {
                    var width = event.pageX - cur.position;

                    if (
                        cfg.resizable &&
                        cfg.minColumnWidth < self.columnsWidth[cur.id] + width &&
                        cfg.minColumnWidth < self.columnsWidth[dep.id] - width
                    ) {
                        if (lastWidth !== width) {
                            self.columnsWidth[cur.id] += width;
                            self.columnsWidth[dep.id] -= width;
                            $(self.columnsElements[cur.id][0]).outerWidth(self.columnsWidth[cur.id]);
                            $(self.columnsElements[dep.id][0]).outerWidth(self.columnsWidth[dep.id]);
                            lastWidth = width;
                            cur.position = event.pageX;
                        }
                    } else if (width <= -(self.columnsWidth[cur.id] - cfg.minColumnWidth)) {
                        $(self.columnsElements[cur.id][0]).outerWidth(cfg.minColumnWidth);
                        $(self.columnsElements[dep.id][0]).outerWidth(
                            self.columnsWidth[dep.id] +
                            self.columnsWidth[cur.id] -
                            cfg.minColumnWidth
                        );
                    } else if (width >= self.columnsWidth[dep.id] - cfg.minColumnWidth) {
                        $(self.columnsElements[dep.id][0]).outerWidth(cfg.minColumnWidth);
                        $(self.columnsElements[cur.id][0]).outerWidth(
                            self.columnsWidth[cur.id] +
                            self.columnsWidth[dep.id] -
                            cfg.minColumnWidth
                        );
                    }
                },

                /**
                 * Mouse up handler
                 * @param {Object} event
                 */
                resizeMouseup = function (event) {
                    cfg.resizable = false;

                    $(event.target).closest('.data-grid').removeClass('_in-resize');
                    $('body').unbind('mousemove', resizeMousemove);
                    $(window).unbind('mouseup', resizeMouseup);
                },

                /**
                 * Mouse down handler
                 * @param {Object} event
                 */
                resizeMousedown = function (event) {
                    var target = event.target;

                    cur.id = self.getColumnId(event);
                    cur.position = event.pageX;
                    event.stopPropagation();
                    dep = self.getDepElement(cur.id, returnObject);
                    cur.element = $(target).parent().attr(cfg.nameSpacing.cellsDataAttribute);
                    cfg.resizable = true;

                    $(target).closest('.data-grid').addClass('_in-resize');
                    $('body').bind('mousemove', resizeMousemove);
                    $(window).bind('mouseup', resizeMouseup);
                };

            $('[' + divAttrProp + '=' + divAttrPropValue + ']').bind('mousedown', resizeMousedown);
        },

        /**
         * @event mouse enter
         * @param {Object} event
         * @returns {Number} Column ID
         */
        getColumnId: function (event) {
            var attr = $(event.target).parent().attr(this.resizeConfig.nameSpacing.cellsDataAttribute);

            return Number(attr.match(/\d+/));
        },

        /**
         * @param {Number} index - column ID
         * @returns {String} Data attribute
         */
        getAttributeName: function (index) {
            return this.resizeConfig.nameSpacing.cellsDataAttrPrefix + index;
        },

        /**
         * Find dependency element
         * @param {Number} index - current element index
         * @param {Boolean} typeObject - type returned value (Object or string)
         */
        getDepElement: function (index, typeObject) {
            index++;

            if (this.isColumnVisible(index)) {
                return typeObject ?
                {
                    element: this.getAttributeName(index),
                    id: index
                }
                    : this.getAttributeName(index);
            }

            return this.getDepElement.apply(this, arguments);
        },

        /**
         * Check visibility column
         * @param {Number} id - column id
         * @returns {Boolean}
         */
        isColumnVisible: function (id) {
            return this.resizeConfig.columnsVisibility[id];
        },

        /**
         * Check is text node or not
         * @param {Object} node - element
         * @returns {Boolean}
         */
        isTextNode: function (node) {
            return !node.html().match(/<(([^>]|\n)*)>/);
        },

        /**
         * @see Set table element to context and start listen events
         * @param {Object} elem - table element
         */
        setTable: function (elem) {
            this.table = elem;
            this.eventListener();
        },

        /**
         * @see Set column visibility to array
         * @param {Object} elem
         * @param {Object} viewModel - view model object
         */
        setColumn: function (elem, viewModel) {
            viewModel.on('visible', this.refreshResizeConfig);
            this.resizeConfig.columnsVisibility.push(viewModel.visible());
        },

        /**
         * @see set cells collection property to config
         * @returns {Object} Chainable
         */
        initCellsCollection: function () {
            var table = $(this.table);

            this.cellsCollection = $.merge(table.find('th'), table.find('td'));

            return this;
        },

        /**
         * @see set rows collection property to config
         * @returns {Object} Chainable
         */
        initRowsCollection: function (rowsCollection) {
            this.rowsCollection = rowsCollection || $(this.table).find('tr');

            return this;
        },

        /**
         * @see set columnsLength property to config
         * @param {Array} cellsCollection - cells collection
         * @param {Array} rowsCollection - rows collections
         * @returns {Object} Chainable
         */
        initColumnsLength: function (cellsCollection, rowsCollection) {
            var cc = cellsCollection || this.cellsCollection,
                rc = rowsCollection || this.rowsCollection;

            this.columnsLength = cc.length / rc.length;

            return this;
        },

        /**
         * @see length cells in one column to config
         * @param {Array} cellsCollection - collection
         * @param {Number} columnsLength - length
         * @returns {Object} Chainable
         */
        initCellsInColumn: function (cellsCollection, columnsLength) {
            var cc = cellsCollection || this.cellsCollection,
                cl = columnsLength || this.columnsLength;

            this.cellsInColumn = cc.length / cl;

            return this;
        },

        /**
         * @see set height for rows
         * @param {Array} rowsElements - rows elements
         * @param {Number} rowsLength
         * @returns {Array} with max height for all rows
         */
        initRowsMaxHeight: function (rowsElements, rowsLength) {
            var re = rowsElements || this.rowsElements,
                rl = rowsLength || this.rowsCollection.length,
                i = 0,
                curRow;

            this.rowsMaxHeight = [];

            for (i; i < rl; i++) {
                curRow = $(re[i]).find('div');
                curRow.css('white-space', 'nowrap');
                this.rowsMaxHeight[i] = curRow.height() * this.resizeConfig.showLines;
                curRow.css('white-space', 'normal');
            }

            return this;
        },

        /**
         * @see add cells elements to current column
         * @param {Array} cellsCollection -  collection
         * @param {Number} columnsLength - columns length
         * @returns {Object} Chainable
         */
        initColumnsElements: function (cellsCollection, columnsLength) {
            var i = 0,
                j = 0,
                cc = cellsCollection || this.cellsCollection,
                cl = columnsLength || this.columnsLength,
                length = cc.length;

            this.columnsElements = [];

            for (i; i < length; i++) {
                i !== 0 ? j++ : j = 0;

                if (j >= cl) {
                    j = 0;
                }

                i <= j ? this.columnsElements[j] = [] : false;
                this.columnsElements[j].push(cc[i]);
            }

            return this;
        },

        /**
         * @see take columns width and push to array
         * @param {Array} columnsElement - columns
         * @param {Number} columnsLength
         * @returns {Object} Chainable
         */
        initColumnsWidth: function (columnsElement, columnsLength) {
            var ce = columnsElement || this.columnsElements,
                length = columnsLength || this.columnsLength,
                array = [],
                i = 0;

            for (i; i < length; i++) {
                array.push($(ce[i][0]).outerWidth());
            }
            this.columnsWidth = array;

            return this;
        },

        /**
         * @see add rows elements to current rows
         * @param {Array} rowsCollection - rows collections
         * @param {Number} rowsLength - rows length
         * @returns {Object} Chainable
         */
        initRowsElements: function (rowsCollection, rowsLength) {
            var rc = rowsCollection || this.rowsCollection,
                rl = rowsLength || this.rowsCollection.length,
                curRow,
                i = 0;

            this.rowsElements = [];

            for (i; i < rl; i++) {
                curRow = $(rc[i]);
                this.rowsElements[i] = $.merge(curRow.find('th'), curRow.find('td'));
            }

            return this;
        },

        /**
         * @see add data attributes for cells collections
         * @param {Array} columnsElements - array with columns elements
         * @param {String} cellsDataAttribute - attribute name
         * @param {String} cellsDataAttrPrefix - attribute prefix
         * @param {Number} cellsInColumn - length cells in one column
         * @returns {Object} Chainable
         */
        setDataAttribute: function (columnsElements, cellsDataAttribute, cellsDataAttrPrefix, cellsInColumn) {
            var ce = columnsElements || this.columnsElements,
                cic = cellsInColumn || this.cellsInColumn,
                cda = cellsDataAttribute || this.resizeConfig.nameSpacing.cellsDataAttribute,
                cdp = cellsDataAttrPrefix || this.resizeConfig.nameSpacing.cellsDataAttrPrefix,
                i = 0,
                j,
                lengthElements = ce.length;

            for (i; i < lengthElements; i++) {
                j = 0;

                for (j; j < cic; j++) {
                    $(ce[i][j]).attr(cda, cdp + i);
                }
            }

            return this;
        },

        setRowsMaxHeight: function (rowsElements, rowsMaxHeight, rowsLength) {
            var re = rowsElements || this.rowsElements,
                rl = rowsLength || this.rowsCollection.length,
                rmh = rowsMaxHeight || this.rowsMaxHeight,
                i = 0,
                j = 0,
                curNode;

            for (i; i < rl; i++) {
                j = 0;

                for (j; j < re[i].length; j++) {
                    curNode = $(re[i][j]).find('div');
                    !curNode.length ? curNode = $(re[i][j]) : false;

                    if (this.isTextNode(curNode)) {
                        curNode.css({
                            maxHeight: rmh[i],
                            overflow: 'hidden'
                        });
                    }
                }
            }

            return this;
        },

        /**
         * @see Init config
         * @returns {Object} Chainable
         */
        initResizeConfig: function () {
            this
                .initCellsCollection()
                .initRowsCollection()
                .initColumnsLength()
                .initCellsInColumn()
                .initColumnsElements()
                .initColumnsWidth()
                .initRowsElements()
                .initRowsMaxHeight()

                .setDataAttribute();
                //.setRowsMaxHeight();

            this.resizeConfig.entryConfig.state = true;
            this.resizeConfig.entryConfig.promise.resolve();

            return this;
        },

        /**
         * @see Refresh application data
         */
        refreshResizeConfig: function () {},

        getMaxHeight: function () {
            console.log('getMaxHeight');
            return 40;
        },

        onSortChange: function () {
            console.log('sortchange')
            this.setRowsMaxHeight();
        }
    });
});
