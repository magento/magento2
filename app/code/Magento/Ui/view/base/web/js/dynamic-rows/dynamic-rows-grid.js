/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'mageUtils',
    'underscore',
    'uiLayout',
    './dynamic-rows'
], function (ko, utils, _, layout, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            map: null,
            cacheGridData: [],
            dataLength: 0,
            listens: {
                'insertData': 'processingInsertData',
                'elems': 'mappingValue'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertData'
                ]);

            return this;
        },

        /**
         * Initialize children,
         * set data from server to grid dataScope
         */
        initChildren: function () {
            var insertData = [];

            if (this.recordData().length) {
                this.recordData.each(function (recordData) {
                    insertData.push(this.unmappingValue(recordData));
                }, this);

                this.source.set(this.dataProvider, insertData);
            }

            return this;
        },

        /**
         * Unmapping value,
         * unmapping data from server to grid dataScope
         *
         * @param {Object} data - data object
         */
        unmappingValue: function (data) {
            var obj = {};

            _.each(this.map, function (prop, index) {
                obj[prop] = data[index];
            }, this);

            return obj;
        },

        /**
         * Rerender dynamic-rows elems
         */
        reload: function () {
            this.cacheGridData = [];
            this._super();
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertData: function (data) {

            if (!data) {
                return false;
            }

            var changes = this._checkGridData(data);

            this.cacheGridData = data;

            changes.each(function (changedObject) {
                this.addChild(changedObject, false, parseInt(changedObject[this.map.id], 10));
            }, this);
        },

        /**
         * Delete record instance
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         */
        deleteRecord: function (index, recordId) {
            var data = this.getElementData(this.insertData(), recordId),
                lastRecord =
                    _.findWhere(this.elems(), {index: this.recordIterator-1}) ||
                    _.findWhere(this.elems(), {index: (this.recordIterator-1).toString()}),
                recordsData;

            this.mapping = true;
            lastRecord.destroy();
            this.removeMaxPosition();
            this.insertData(_.reject(this.source.get(this.dataProvider), function (recordData) {
                return parseInt(recordData[this.map.id], 10) === parseInt(data[this.map.id], 10);
            }, this));
            recordsData = _.reject(this.source.get(this.dataScope + '.' + this.index), function (recordData) {
                return parseInt(recordData.id, 10) === parseInt(recordId, 10);
            }, this);
            this._updateData(recordsData);
            this._sortAfterDelete();
            --this.recordIterator;
            this.mapping = false;
        },

        /**
         * Set new data to dataSource,
         * delete element
         *
         * @param {Object} data - record data
         */
        _updateData: function (data) {
            var elems = utils.copy(this.elems()),
                path;

            this.recordData([]);
            elems = utils.copy(this.elems());
            data.each(function (rec, idx) {
                elems[idx].recordId = rec.id;
                path = this.dataScope + '.' + this.index + '.' + idx;
                this.source.set(path, rec);
            }, this);
            this.elems(elems);
        },

        /**
         * Sort elems by position property
         */
        _sortAfterDelete: function () {
            this.elems(this.elems().sort(function (propOne, propTwo) {
                return parseInt(propOne.position, 10) - parseInt(propTwo.position, 10);
            }));
        },

        /**
         * Add child components, call parent
         *
         * @param {Object} data - component data
         * @param {Number} index - record index
         * @param {Number} prop - grid record id
         *
         * @returns {Object} Chainable.
         */
        addChild: function (data, index, prop) {
            var template = this.templates.record;

            _.extend(this.templates.record, {
                recordId: prop
            });

            this._super();
            this.templates.record = template;

            return this;
        },

        /**
         * Check changed records
         *
         * @param {Array} data - array with records data
         * @returns {Array} Changed records
         */
        _checkGridData: function (data) {
            var cacheLength = this.cacheGridData.length,
                curData = data.length,
                max = cacheLength > curData ? this.cacheGridData : data,
                changes = [],
                obj = {};

            max.each(function (record, index) {
                obj[this.map.id] = record[this.map.id];
                if (!_.where(this.cacheGridData, obj).length) {
                    changes.push(data[index]);
                }
            }, this);

            return changes;
        },

        /**
         * Mapped value
         */
        mappingValue: function () {
            var path,
                data,
                elements = this.elems();

            if (this.mapping) {
                return false;
            }

            elements.each(function (record) {
                data = this.getElementData(this.insertData(), record.recordId);

                _.each(this.map, function (prop, index) {
                    path = record.dataScope + '.' + index;
                    this.source.set(path, data[prop]);
                }, this);
            }, this);
        },

        /**
         * Find data object by index
         *
         * @param {Array} array - data collection
         * @param {Number} index - element index
         * @param {String} property - to find by property
         *
         * @returns {Object} data object
         */
        getElementData: function (array, index, property) {
            var obj = {},
                result;

            property ? obj[property] = index : obj[this.map.id] = index;
            result = _.findWhere(array, obj);
            !result ? property ? obj[property] = index.toString() : obj[this.map.id] = index.toString() : false;
            result = _.findWhere(array, obj);

            return result;
        }
    });
});