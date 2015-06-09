/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/class'
], function (ko, $, _, Class) {
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
        return ko.contextFor(elem).$data;
    }

    return Class.extend({
        defaults: {
            noSelectClass: '_no-select',
            hiddenClass: '_hidden',
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
            _.bindAll(this, 'onMouseMove', 'onMouseUp', 'onMouseDown');

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
            var addListener = document.addEventListener;

            if (isTouchDevice) {
                addListener('touchmove', this.onMouseMove, false);
                addListener('touchend', this.onMouseUp, false);
                addListener('touchleave', this.onMouseUp, false);
            } else {
                addListener('mousemove', this.onMouseMove, false);
                addListener('mouseup', this.onMouseUp, false);
            }

            return this;
        },

        /**
         * Sets specified column as a draggable element.
         *
         * @param {HTMLTableHeaderCellElement} column - Columns header element.
         * @returns {Dnd} Chainable.
         */
        addColumn: function (column) {
            this.columns.push(column);

            isTouchDevice ?
                column.addEventListener('touchstart', this.onMouseDown, false) :
                column.addEventListener('mousedown', this.onMouseDown, false);

            return this;
        },

        /**
         * Defines specified table element as a main container.
         *
         * @param {HTMLTableElement} table
         * @returns {Dnd} Chainable.
         */
        setTable: function (table) {
            this.table = table;

            return this;
        },

        /**
         * Defines specified table element as a draggable table.
         * Only this element will be moved across the screen.
         *
         * @param {HTMLTableElement} dragTable
         * @returns {Dnd} Chainable.
         */
        setDragTable: function (dragTable) {
            this.dragTable = dragTable;

            return this;
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
         * Coppies dimensions of a grabbed column
         * to a draggable grid.
         *
         * @param {HTMLTableHeaderCellElement} elem - Grabbed column.
         * @returns {Dnd} Chainable.
         */
        _copyDimensions: function (elem) {
            var dragTable   = this.dragTable,
                dragBody    = dragTable.tBodies[0],
                dragTrs     = dragBody ? dragBody.children : [],
                origTrs     = _.toArray(this.table.tBodies[0].children),
                columnIndex = _.toArray(elem.parentNode.cells).indexOf(elem),
                origTd,
                dragTr;

            dragTable.style.width = elem.offsetWidth + 'px';
            dragTable.tHead.firstElementChild.cells[0].style.height = elem.offsetHeight + 'px';

            origTrs.forEach(function (origTr, rowIndex) {
                origTd = origTr.cells[columnIndex];
                dragTr = dragTrs[rowIndex];

                if (origTd && dragTr) {
                    dragTr.cells[0].style.height = origTd.offsetHeight + 'px';
                }
            });

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
                ._copyDimensions(elem);

            $(this.dragTable).removeClass(this.hiddenClass);
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

            $(this.dragTable).addClass(this.hiddenClass);

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
