/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './dynamic-rows'
], function (_, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            map: null,
            cacheGridData: [],
            deleteProperty: false,
            dataLength: 0,
            identificationProperty: 'id',
            listens: {
                'insertData': 'processingInsertData',
                'recordData': 'initElements setToInsertData'
            }
        },

        initObservable: function () {
            this._super()
                .observe([
                    'insertData'
                ]);

            return this;
        },

        setToInsertData: function () {
            var insertData = [],
                obj;

            if (this.recordData().length && !this.update) {
                this.recordData.each(function (recordData) {
                    obj = {};
                    obj[this.map[this.identificationProperty]] = recordData[this.identificationProperty];
                    insertData.push(obj);
                }, this);

                this.source.set(this.dataProvider, insertData);
            }
        },

        initChildren: function () {
            this.getChildItems().each(function (data, index) {
                this.pageSizeChecker(data, this.startIndex + index, data.id)
            }, this);

            return this;
        },

        initElements: function (data) {
            var newData = this.getNewData(data);

            this.getPagesData(data);

            if (newData.length) {
                if (this.insertData().length) {
                    this.processingAddChild(newData[0], data.length - 1 , newData[0].id);
                }
            }

            return this;
        },

        /**
         * Delete record instance
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         */
        deleteRecord: function (index, recordId) {
            var data = this.getElementData(this.insertData(), recordId);

            this._super();
            this.insertData(_.reject(this.source.get(this.dataProvider), function (recordData) {
                return parseInt(recordData[this.map.id], 10) === parseInt(data[this.map.id], 10);
            }, this));
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
        },

        processingAddChild: function (ctx, index, prop) {
            if (this._elems.length > this.pageSize) {
                return false;
            }

            this.addChild(ctx, index, prop);
        },

        getNewData: function (data) {
            var changes = [];

            if (data.length !== this.relatedData) {
                data.each(function(obj) {
                    if (!_.findWhere(this.relatedData, {id: obj.id})) {
                        changes.push(obj);
                    }
                }, this)
            }

            return changes;
        },

        processingInsertData: function (data) {
            var changes,
                id;

            changes = this._checkGridData(data);
            this.cacheGridData = data;

            changes.each(function (changedObject) {
                id = parseInt(changedObject[this.map.id], 10);
                this.mappingValue(changedObject)
            }, this);
        },

        mappingValue: function (data) {
            var obj = {};

            _.each(this.map, function (prop, index) {
                obj[index] = data[prop];
            }, this);

            if (_.findWhere(this.recordData(), {id: obj.id})) {
                return false;
            }

            this.source.set(this.dataScope + '.' + this.index + '.' + this.recordData().length, obj);
        },

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
        }
    });
});
