/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'underscore',
    'uiElement',
    'Magento_Ui/js/lib/view/utils/async'
], function (ko, $, _, Element) {
    'use strict';

    var transformProp;

    /**
     * Get element context
     */
    function getContext(elem) {
        return ko.contextFor(elem);
    }

    /**
     * Defines supported css 'transform' property.
     *
     * @returns {String|Undefined}
     */
    transformProp = (function () {
        var style = document.createElement('div').style,
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

    return Element.extend({
        defaults: {
            rootSelector: '${ $.recordsProvider }:div.admin__field',
            tableSelector: '${ $.rootSelector } -> table.admin__dynamic-rows',
            separatorsClass: {
                top: '_dragover-top',
                bottom: '_dragover-bottom'
            },
            step: 'auto',
            recordsCache: [],
            draggableElement: {},
            draggableElementClass: '_dragged',
            listens: {
                '${ $.recordsProvider }:elems': 'setCacheRecords'
            }
        },

        /**
         * Initialize component
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            _.bindAll(
                this,
                'initTable',
                'mousedownHandler',
                'mousemoveHandler',
                'mouseupHandler'
            );

            this._super()
                .body = $('body');
            $.async(this.tableSelector, this.initTable);

            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'recordsCache'
                ]);

            return this;
        },

        /**
         * Initialize table
         *
         * @param {Object} table - table element
         */
        initTable: function (table) {
            if (!this.table) {
                this.table = $(table);
                this.tableWrapper = this.table.parent();
            }
        },

        /**
         * Mouse down handler
         *
         * @param {Object} data - element data
         * @param {Object} elem - element
         * @param {Object} event - key down event
         */
        mousedownHandler: function (data, elem, event) {
            var recordNode = this.getRecordNode(elem),
                originRecord = $(elem).parents('tr'),
                drEl = this.draggableElement;

            $(recordNode).addClass(this.draggableElementClass);
            $(originRecord).addClass(this.draggableElementClass);
            this.step = this.step === 'auto' ? originRecord.height() / 2 : this.step;
            drEl.originRow = originRecord;
            drEl.instance = recordNode = this.processingStyles(recordNode, elem);
            drEl.instanceCtx = this.getRecord(originRecord[0]);
            drEl.eventMousedownY = event.pageY;
            drEl.minYpos =
                this.table.offset().top - originRecord.offset().top +
                this.table.outerHeight() - this.table.find('tbody').outerHeight();
            drEl.maxYpos = drEl.minYpos + this.table.find('tbody').outerHeight() - originRecord.outerHeight();
            this.tableWrapper.append(recordNode);
            this.body.bind('mousemove', this.mousemoveHandler);
            this.body.bind('mouseup', this.mouseupHandler);
        },

        /**
         * Mouse move handler
         *
         * @param {Object} event - mouse move event
         */
        mousemoveHandler: function (event) {
            var depEl = this.draggableElement,
                positionY = event.pageY - depEl.eventMousedownY,
                processingPositionY = positionY + 'px',
                processingMaxYpos = depEl.maxYpos + 'px',
                processingMinYpos = depEl.minYpos + 'px',
                depElement = this.getDepElement(depEl.instance, positionY);

            if (depElement) {
                depEl.depElement ? depEl.depElement.elem.removeClass(depEl.depElement.className) : false;
                depEl.depElement = depElement;
                depEl.depElement.insert !== 'none' ? depEl.depElement.elem.addClass(depElement.className) : false;
            } else if (depEl.depElement && depEl.depElement.insert !== 'none') {
                depEl.depElement.elem.removeClass(depEl.depElement.className);
                depEl.depElement.insert = 'none';
            }

            if (positionY > depEl.minYpos && positionY < depEl.maxYpos) {
                $(depEl.instance)[0].style[transformProp] = 'translateY(' + processingPositionY + ')';
            } else if (positionY < depEl.minYpos) {
                $(depEl.instance)[0].style[transformProp] = 'translateY(' + processingMinYpos + ')';
            } else if (positionY >= depEl.maxYpos) {
                $(depEl.instance)[0].style[transformProp] = 'translateY(' + processingMaxYpos + ')';
            }
        },

        /**
         * Mouse up handler
         */
        mouseupHandler: function () {
            var depElementCtx,
                drEl = this.draggableElement;

            if (drEl.depElement) {
                depElementCtx = this.getRecord(drEl.depElement.elem[0]);
                drEl.depElement.elem.removeClass(drEl.depElement.className);

                if (drEl.depElement.insert !== 'none') {
                    this.setPosition(drEl.depElement.elem, depElementCtx, drEl);
                }
            }

            drEl.originRow.removeClass(this.draggableElementClass);
            this.body.unbind('mousemove', this.mousemoveHandler);
            this.body.unbind('mouseup', this.mouseupHandler);
            drEl.instance.remove();
            this.draggableElement = {};
        },

        /**
         * Set position to element
         *
         * @param {Object} depElem - dep element
         * @param {Object} depElementCtx - dep element context
         * @param {Object} dragData - data draggable element
         */
        setPosition: function (depElem, depElementCtx, dragData) {
            var depElemPosition = parseInt(depElementCtx.position, 10);

            if (dragData.depElement.insert === 'after') {
                dragData.instanceCtx.position = depElemPosition + 1;
            } else if (dragData.depElement.insert === 'before') {
                dragData.instanceCtx.position = depElemPosition;
            }
        },

        /**
         * Get dependency element
         *
         * @param {Object} curInstance - current element instance
         * @param {Number} position
         */

        getDepElement: function (curInstance, position) {
            var recordsCollection = this.table.find('tbody > tr'),
                curInstancePositionTop = $(curInstance).position().top,
                curInstancePositionBottom = curInstancePositionTop + $(curInstance).height();

            if (position < 0) {
                return this._getDepElement(recordsCollection, 'before', curInstancePositionTop);
            } else if (position > 0) {
                return this._getDepElement(recordsCollection, 'after', curInstancePositionBottom);
            }
        },

        /**
         * Get dependency element private
         *
         * @param {Array} collection - record collection
         * @param {String} position - position to add
         * @param {Number} dragPosition - position drag element
         */
        _getDepElement: function (collection, position, dragPosition) {
            var rec,
                rangeEnd,
                rangeStart,
                result,
                className,
                i = 0,
                length = collection.length;

            for (i; i < length; i++) {
                rec = collection.eq(i);

                if (position === 'before') {
                    rangeStart = collection.eq(i).position().top;
                    rangeEnd = rangeStart + this.step;
                    className = this.separatorsClass.top;
                } else if (position === 'after') {
                    rangeEnd = rec.position().top + rec.height();
                    rangeStart = rangeEnd - this.step;
                    className = this.separatorsClass.bottom;
                }

                if (dragPosition > rangeStart && dragPosition < rangeEnd) {
                    result = {
                        elem: rec,
                        insert: rec[0] === this.draggableElement.originRow[0] ? 'none' : position,
                        className: className
                    };
                }
            }

            return result;
        },

        /**
         * Set default position of draggable element
         *
         * @param {Object} elem - current element instance
         * @param {Object} data - current element data
         */
        _setDefaultPosition: function (elem, data) {
            var originRecord = $(elem).parents('tr'),
                position = originRecord.position();

            ++position.top;
            $(data).css(position);
        },

        /**
         * Set records to cache
         *
         * @param {Object} records - record instance
         */
        setCacheRecords: function (records) {
            this.recordsCache(records);
        },

        /**
         * Set styles to draggable element
         *
         * @param {Object} data - data
         * @param {Object} elem - elem instance
         * @returns {Object} instance data.
         */
        processingStyles: function (data, elem) {
            var table = this.table,
                columns = table.find('th'),
                recordColumns = $(data).find('td');

            this._setDefaultPosition(elem, $(data));
            this._setColumnsWidth(columns, recordColumns);
            this._setTableWidth(table, $(data));

            return data;
        },

        /**
         * Set table width.
         *
         * @param {Object} originalTable - original record instance
         * @param {Object} recordTable - draggable record instance
         */
        _setTableWidth: function (originalTable, recordTable) {
            recordTable.outerWidth(originalTable.outerWidth());
        },

        /**
         * Set columns width.
         *
         * @param {Object} originColumns - original record instance
         * @param {Object} recordColumns - draggable record instance
         */
        _setColumnsWidth: function (originColumns, recordColumns) {
            var i = 0,
                length = originColumns.length;

            for (i; i < length; i++) {
                recordColumns.eq(i).outerWidth(originColumns.eq(i).outerWidth());
            }
        },

        /**
         * Get copy original record
         *
         * @param {Object} record - original record instance
         * @returns {Object} draggable record instance
         */
        getRecordNode: function (record) {
            var table = this.table[0].cloneNode(true);

            $(table).find('tr').remove();
            $(table).append($(record).parents('tr')[0].cloneNode(true));

            return table;
        },

        /**
         * Get record context by element
         *
         * @param {Object} elem - original element
         * @returns {Object} draggable record context
         */
        getRecord: function (elem) {
            return this.recordsCache()[getContext(elem).$index()];
        }

    });
});
