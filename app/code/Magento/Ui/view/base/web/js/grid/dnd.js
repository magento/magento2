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
     * Defines vendor prefix for the css 'transform' property.
     */
    transformProp = (function () {
        var style = document.body.style,
            base = 'Transform',
            vendors = ['webkit', 'moz', 'ms', 'o'],
            vi = vendors.length,
            property;

        if (typeof style.transform !== 'undefined') {
            return 'transform';
        }

        while (vi--) {
            property = vendors[vi] + base;

            if (typeof style[property] !== 'undefined') {
                return property;
            }
        }
    })();

    /**
     * Returns first touch data if it's available.
     *
     * @returns {Object}
     */
    function getTouch(e) {
        return e.touches ? e.touches[0] : e;
    }

    /**
     * Moves specified DOM element to the x and y coordinates.
     *
     * @param {HTMLElement} elem - Element to be relocated.
     * @param {Number} x - Value on the 'x' axis.
     * @param {Number} y - Value on the 'y' axis.
     */
    function locate(elem, x, y) {
        var value = 'translate(' + x + 'px,' + y + 'px)';

        elem.style[transformProp] = value;
    }

    /**
     * Checks if specified coordinate is inside of the provided area.
     *
     * @param {Number} x - Value on the 'x' axis.
     * @param {Number} y - Value on the 'y' axis.
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
            columnsSelector: '',
            dragGridSelector: '',
            noSelectClass: '_no-select',
            hiddenClass: '_hidden',
            fixedX: false,
            fixedY: true,
            minDistance: 2
        },

        /**
         * Initializes Dnd component.
         *
         * @returns {Dnd} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'onMouseMove', 'onMouseUp', 'onMouseDown');

            this._super()
                .initColumns()
                .initListeners();

            return this;
        },

        /**
         * Searches document for the columns elements.
         *
         * @returns {Dnd} Chainable.
         */
        initColumns: function () {
            var columns = this.grid.querySelectorAll(this.columnsSelector);

            this.$body = $('body');

            this.dragGrid = document.querySelector(this.dragGridSelector);
            this.columns = _.toArray(columns);

            return this;
        },

        /**
         * Binds necessary events listeners.
         *
         * @returns {Dnd} Chainbale.
         */
        initListeners: function () {
            if (isTouchDevice) {
                document.addEventListener('touchmove', this.onMouseMove, false);
                document.addEventListener('touchend', this.onMouseUp, false);
                document.addEventListener('touchleave', this.onMouseUp, false);
            } else {
                document.addEventListener('mousemove', this.onMouseMove, false);
                document.addEventListener('mouseup', this.onMouseUp, false);
            }

            this.columns.forEach(function (column) {
                isTouchDevice ?
                    column.addEventListener('touchstart', this.onMouseDown, false) :
                    column.addEventListener('mousedown', this.onMouseDown, false);
            }, this);

            return this;
        },

        /**
         * Calculates coordinates of draggable elements.
         *
         * @returns {Dnd} Chainbale.
         */
        _cacheCoords: function () {
            var container   = this.grid.getBoundingClientRect(),
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
            var dragGrid    = this.dragGrid,
                dragTrs     = dragGrid.tBodies[0].children,
                origTrs     = _.toArray(this.grid.tBodies[0].children),
                columnIndex = _.toArray(elem.parentNode.cells).indexOf(elem),
                origTd;

            dragGrid.style.width = elem.offsetWidth + 'px';
            dragGrid.tHead.firstElementChild.cells[0].style.height = elem.offsetHeight + 'px';

            origTrs.forEach(function (origTr, rowIndex) {
                origTd = origTr.cells[columnIndex];

                if (origTd) {
                    dragTrs[rowIndex].cells[0].style.height = origTd.offsetHeight + 'px';
                }
            });

            return this;
        },

        /**
         * Matches provided coordinates to available areas.
         *
         * @param {Number} x - X coordinate.
         * @param {Number} y - Y coordinate.
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
         * @param {Number} x
         * @param {Number} y
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
         * Dragstart action handler.
         *
         * @param {HTMLElement} elem - Element which is dragging.
         */
        dragstart: function (elem) {
            this.initDrag = false;
            this.dropArea = false;
            this.dragging = true;

            getModel(elem).dragging(true);

            this._cacheCoords()
                ._copyDimensions(elem);

            $(this.dragGrid).removeClass(this.hiddenClass);
        },

        /**
         * Dragend action handler.
         *
         * @param {HTMLElement} elem - Element that was dragged.
         */
        dragend: function (elem) {
            var area = this.dropArea;

            this.$body.removeClass(this.noSelectClass);

            this.initDrag = false;

            if (!this.dragging) {
                return;
            }

            this.dragging = false;

            $(this.dragGrid).addClass(this.hiddenClass);

            getModel(elem).dragging(false);

            if (area && area.target !== elem) {
                this.drop(area.target, elem);
            }
        },

        /**
         * Dragenter action handler.
         *
         * @param {Object} area
         */
        dragenter: function (area) {
            var elem        = area.target,
                drag        = this.dragArea,
                direction   = drag.index < area.index ? 'left' : 'right';

            getModel(elem).dragover(direction);
        },

        /**
         * Dragleave action handler.
         *
         * @param {Object} area
         */
        dragleave: function (area) {
            getModel(area.target).dragover(false);
        },

        /**
         * Drop action handler.
         *
         * @param {HTMLElement} target
         * @param {HTMLElement} elem
         */
        drop: function (target, elem) {
            target = getModel(target);
            elem = getModel(elem);

            getModel(this.grid).insertChild(elem, target);
            target.dragover(false);
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

            locate(this.dragGrid, posX, posY);

            if (!isInside(x, y, this.dropArea)) {
                this._updateAreas(x, y);
            }
        },

        /**
         * Grab action handler.
         *
         * @param {Number} x - Coordinate of a grabbed point on 'x' axis.
         * @param {Number} y - Coordinate of a grabbed point on 'y' axis.
         * @param {HTMLElement} target - Grabbed elemenet.
         */
        grab: function (x, y, target) {
            this.initDrag = true;
            this.grabbed = {
                x: x,
                y: y,
                elem: target
            };

            this.$body.addClass(this.noSelectClass);
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
                this.dragend(this.grabbed.elem);
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
