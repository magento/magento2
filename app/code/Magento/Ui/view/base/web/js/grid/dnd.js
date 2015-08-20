/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/lib/ko/extender/bound-nodes',
    'mage/utils/dom-observer',
    'uiClass'
], function (ko, $, _, registry, boundedNodes, domObserver, Class) {
    'use strict';

    var isTouchDevice = typeof document.ontouchstart !== 'undefined',
        transformProp;

    /**
     * Defines supported css 'transform' property.
     *
     * @returns {String|Undefined}
     */
    transformProp = (function () {
        var style = document.body.style,
            base = 'Transform',
            vendors = ['webkit', 'moz', 'ms', 'o'],
            vi = vendors.length,
            property;

        if (typeof style.transform != 'undefined') {
            return 'transform';
        }

        while (vi--) {
            property = vendors[vi] + base;

            if (typeof style[property] != 'undefined') {
                return property;
            }
        }
    })();

    /**
     * Returns first touch data if it's available.
     *
     * @param {(MouseEvent|TouchEvent)} e - Event object.
     * @returns {Object}
     */
    function getTouch(e) {
        return e.touches ? e.touches[0] : e;
    }

    /**
     * Moves specified DOM element to the x and y coordinates.
     *
     * @param {HTMLElement} elem - Element to be relocated.
     * @param {Number} x - X coordinate.
     * @param {Number} y - Y coordinate.
     */
    function locate(elem, x, y) {
        var value = 'translate(' + x + 'px,' + y + 'px)';

        elem.style[transformProp] = value;
    }

    /*eslint-disable no-extra-parens*/
    /**
     * Checks if specified coordinate is inside of the provided area.
     *
     * @param {Number} x - X coordinate.
     * @param {Number} y - Y coordinate.
     * @param {Object} area - Object which represents area.
     * @returns {Boolean}
     */
    function isInside(x, y, area) {
        return (
            area &&
            x >= area.left && x <= area.right &&
            y >= area.top && y <= area.bottom
        );
    }
    /*eslint-enable no-extra-parens*/

    /**
     * Calculates distance between two points.
     *
     * @param {Number} x1 - X coordinate of a first point.
     * @param {Number} y1 - Y coordinate of a first point.
     * @param {Number} x2 - X coordinate of a second point.
     * @param {Number} y2 - Y coordinate of a second point.
     * @returns {Number} Distance between points.
     */
    function distance(x1, y1, x2, y2) {
        var dx = x2 - x1,
            dy = y2 - y1;

        dx *= dx;
        dy *= dy;

        return Math.sqrt(dx + dy);
    }

    /**
     * Returns viewModel associated with a provided DOM element.
     *
     * @param {HTMLElement} elem
     * @returns {Object|Array}
     */
    function getModel(elem) {
        return ko.dataFor(elem);
    }

    return Class.extend({
        defaults: {
            noSelectClass: '_no-select',
            hiddenClass: '_hidden',
            columnSelector: 'thead tr th',
            fixedX: false,
            fixedY: true,
            minDistance: 2,
            columns: []
        },

        /**
         * Initializes Dnd component.
         *
         * @returns {Dnd} Chainable.
         */
        initialize: function () {
            _.bindAll(
                this,
                'initColumn',
                'removeColumn',
                'onMouseMove',
                'onMouseUp',
                'onMouseDown'
            );

            this.$body = $('body');

            this._super()
                .initListeners();

            return this;
        },

        /**
         * Binds necessary events listeners.
         *
         * @returns {Dnd} Chainbale.
         */
        initListeners: function () {
            var initRoot = this.initRoot.bind(this);

            registry.get(this.columnsProvider, function (columns) {
                boundedNodes.get(columns, initRoot);
            });

            if (isTouchDevice) {
                document.addEventListener('touchmove', this.onMouseMove, false);
                document.addEventListener('touchend', this.onMouseUp, false);
                document.addEventListener('touchleave', this.onMouseUp, false);
            } else {
                document.addEventListener('mousemove', this.onMouseMove, false);
                document.addEventListener('mouseup', this.onMouseUp, false);
            }

            return this;
        },

        /**
         * Initializes columns root node.
         *
         * @param {HTMLElement} node
         */
        initRoot: function (node) {
            var table = $('> table.data-grid', node)[0];

            this.initTable(table);
        },

        /**
         * Defines specified table element as a main container.
         *
         * @param {HTMLTableElement} table
         * @returns {Dnd} Chainable.
         */
        initTable: function (table) {
            this.table = table;

            $(table).addClass('data-grid-draggable');

            domObserver.get(this.columnSelector, this.initColumn, table);
            domObserver.remove(this.columnSelector, this.removeColumn, table);

            return this;
        },

        /**
         * Sets specified column as a draggable element.
         *
         * @param {HTMLTableHeaderCellElement} column - Columns header element.
         * @returns {Dnd} Chainable.
         */
        initColumn: function (column) {
            var model = getModel(column);

            if (!model || !model.draggable) {
                return this;
            }

            this.columns.push(column);

            ko.applyBindingsToNode(column, {
                css: {
                    '_dragover-left': ko.computed(function () {
                        return model.dragover() === 'right';
                    }),
                    '_dragover-right': ko.computed(function () {
                        return model.dragover() === 'left';
                    })
                }
            }, model);

            isTouchDevice ?
                column.addEventListener('touchstart', this.onMouseDown, false) :
                column.addEventListener('mousedown', this.onMouseDown, false);

            return this;
        },

        /**
         * Removes specified column element from the columns array.
         *
         * @param {HTMLTableHeaderCellElement} column - Columns header element.
         * @returns {Dnd} Chainable.
         */
        removeColumn: function (column) {
            var columns = this.columns,
                index = columns.indexOf(column);

            if (~index) {
                columns.splice(index, 1);
            }

            return this;
        },

        /**
         * Returns index of column.
         *
         * @param {HTMLTableHeaderCellElement} elem
         * @returns {Number}
         */
        _getColumnIndex: function (elem) {
            return _.toArray(elem.parentNode.cells).indexOf(elem);
        },

        /**
         * Calculates coordinates of draggable elements.
         *
         * @returns {Dnd} Chainbale.
         */
        _cacheCoords: function () {
            var container   = this.table.getBoundingClientRect(),
                bodyRect    = document.body.getBoundingClientRect(),
                grabbed     = this.grabbed,
                dragElem    = grabbed.elem,
                cells       = _.toArray(dragElem.parentNode.cells),
                rect;

            this.coords = this.columns.map(function (column) {
                var data;

                rect = column.getBoundingClientRect();

                data = {
                    index: cells.indexOf(column),
                    target: column,
                    orig: rect,
                    left: rect.left - bodyRect.left,
                    right: rect.right - bodyRect.left,
                    top: rect.top - bodyRect.top,
                    bottom: container.bottom - bodyRect.top
                };

                if (column === dragElem) {
                    this.dragArea = data;

                    grabbed.shiftX = rect.left - grabbed.x;
                    grabbed.shiftY = rect.top - grabbed.y;
                }

                return data;
            }, this);

            return this;
        },

        /**
         * Creates clone of a target table with only specified column visible.
         *
         * @param {HTMLTableHeaderCellElement} elem - Dragging column.
         * @returns {Dnd} Chainbale.
         */
        _cloneTable: function (elem) {
            var clone       = this.table.cloneNode(true),
                columnIndex = this._getColumnIndex(elem),
                headRow     = clone.tHead.firstElementChild,
                headCells   = _.toArray(headRow.cells),
                tableBody   = clone.tBodies[0],
                bodyRows    = _.toArray(tableBody.children),
                origTrs     = this.table.tBodies[0].children;

            clone.style.width = elem.offsetWidth + 'px';

            headCells.forEach(function (th, index) {
                if (index !== columnIndex) {
                    headRow.removeChild(th);
                }
            });

            headRow.cells[0].style.height = elem.offsetHeight + 'px';

            bodyRows.forEach(function (row, rowIndex) {
                var cells = row.cells,
                    cell;

                if (cells.length !== headCells.length) {
                    tableBody.removeChild(row);

                    return;
                }

                cell = row.cells[columnIndex].cloneNode(true);

                while (row.firstElementChild) {
                    row.removeChild(row.firstElementChild);
                }

                cell.style.height = origTrs[rowIndex].cells[columnIndex].offsetHeight + 'px';

                row.appendChild(cell);
            });

            this.dragTable = clone;

            $(clone)
                .addClass('_dragging-copy')
                .appendTo('body');

            return this;
        },

        /**
         * Matches provided coordinates to available areas.
         *
         * @param {Number} x - X coordinate of a mouse pointer.
         * @param {Number} y - Y coordinate of a mouse pointer.
         * @returns {Object|Undefined} Matched area.
         */
        _getDropArea: function (x, y) {
            return _.find(this.coords, function (area) {
                return isInside(x, y, area);
            });
        },

        /**
         * Updates state of hovered areas.
         *
         * @param {Number} x - X coordinate of a mouse pointer.
         * @param {Number} y - Y coordinate of a mouse pointer.
         */
        _updateAreas: function (x, y) {
            var leavedArea = this.dropArea,
                area = this.dropArea = this._getDropArea(x, y);

            if (leavedArea) {
                this.dragleave(leavedArea);
            }

            if (area && area.target !== this.dragArea.target) {
                this.dragenter(area);
            }
        },

        /**
         * Grab action handler.
         *
         * @param {Number} x - X coordinate of a grabbed point.
         * @param {Number} y - Y coordinate of a grabbed point.
         * @param {HTMLElement} elem - Grabbed elemenet.
         */
        grab: function (x, y, elem) {
            this.initDrag = true;
            this.grabbed = {
                x: x,
                y: y,
                elem: elem
            };

            this.$body.addClass(this.noSelectClass);
        },

        /**
         * Dragstart action handler.
         *
         * @param {HTMLTableHeaderCellElement} elem - Element which is dragging.
         */
        dragstart: function (elem) {
            this.initDrag = false;
            this.dropArea = false;
            this.dragging = true;

            getModel(elem).dragging(true);

            this._cacheCoords()
                ._cloneTable(elem);
        },

        /**
         * Drag action handler. Locates draggable
         * grid at a specified coordinates.
         *
         * @param {Number} x - X coordinate.
         * @param {Number} y - Y coordinate.
         */
        drag: function (x, y) {
            var grabbed  = this.grabbed,
                dragArea = this.dragArea,
                posX     = x + grabbed.shiftX,
                posY     = y + grabbed.shiftY;

            if (this.fixedX) {
                x    = dragArea.left;
                posX = dragArea.orig.left;
            }

            if (this.fixedY) {
                y    = dragArea.top;
                posY = dragArea.orig.top;
            }

            locate(this.dragTable, posX, posY);

            if (!isInside(x, y, this.dropArea)) {
                this._updateAreas(x, y);
            }
        },

        /**
         * Dragenter action handler.
         *
         * @param {Object} dropArea
         */
        dragenter: function (dropArea) {
            var direction = this.dragArea.index < dropArea.index ?
                'left' :
                'right';

            getModel(dropArea.target).dragover(direction);
        },

        /**
         * Dragleave action handler.
         *
         * @param {Object} dropArea
         */
        dragleave: function (dropArea) {
            getModel(dropArea.target).dragover(false);
        },

        /**
         * Dragend action handler.
         *
         * @param {Object} dragArea
         */
        dragend: function (dragArea) {
            var dropArea = this.dropArea,
                dragElem = dragArea.target;

            this.dragging = false;

            document.body.removeChild(this.dragTable);

            getModel(dragElem).dragging(false);

            if (dropArea && dropArea.target !== dragElem) {
                this.drop(dropArea, dragArea);
            }
        },

        /**
         * Drop action handler.
         *
         * @param {Object} dropArea
         * @param {Object} dragArea
         */
        drop: function (dropArea, dragArea) {
            var dropModel = getModel(dropArea.target),
                dragModel = getModel(dragArea.target);

            getModel(this.table).insertChild(dragModel, dropModel);
            dropModel.dragover(false);
        },

        /**
         * Documents' 'mousemove' event handler.
         *
         * @param {(MouseEvent|TouchEvent)} e - Event object.
         */
        onMouseMove: function (e) {
            var grab    = this.grabbed,
                touch   = getTouch(e),
                x       = touch.pageX,
                y       = touch.pageY;

            if (this.initDrag || this.dragging) {
                e.preventDefault();
            }

            if (this.initDrag && distance(x, y, grab.x, grab.y) >= this.minDistance) {
                this.dragstart(grab.elem);
            }

            if (this.dragging) {
                this.drag(x, y);
            }
        },

        /**
         * Documents' 'mouseup' event handler.
         */
        onMouseUp: function () {
            if (this.initDrag || this.dragging) {
                this.initDrag = false;
                this.$body.removeClass(this.noSelectClass);
            }

            if (this.dragging) {
                this.dragend(this.dragArea);
            }
        },

        /**
         * Columns' 'mousedown' event handler.
         *
         * @param {(MouseEvent|TouchEvent)} e - Event object.
         */
        onMouseDown: function (e) {
            var touch = getTouch(e);

            this.grab(touch.pageX, touch.pageY, e.currentTarget);
        }
    });
});
