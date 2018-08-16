/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
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
            positionProvider: 'position',
            dataLength: 0,
            identificationProperty: 'id',
            identificationDRProperty: 'id',
            listens: {
                'insertData': 'processingInsertData',
                'recordData': 'initElements setToInsertData'
            },
            mappingSettings: {
                enabled: true,
                distinct: true
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
         * Set data from recordData to insertData
         */
        setToInsertData: function () {
            var insertData = [],
                obj;

            if (this.recordData().length && !this.update) {
                _.each(this.recordData(), function (recordData) {
                    obj = {};
                    obj[this.map[this.identificationProperty]] = recordData[this.identificationProperty];
                    insertData.push(obj);
                }, this);

                if (insertData.length) {
                    this.source.set(this.dataProvider, insertData);
                }
            }
        },

        /**
         * Initialize children
         *
         * @returns {Object} Chainable.
         */
        initChildren: function () {
            this.getChildItems().forEach(function (data, index) {
                this.processingAddChild(data, this.startIndex + index, data[this.identificationDRProperty]);
            }, this);

            return this;
        },

        /**
         * Initialize elements from grid
         *
         * @param {Array} data
         *
         * @returns {Object} Chainable.
         */
        initElements: function (data) {
            var newData = this.getNewData(data);

            this.parsePagesData(data);

            if (newData.length) {
                if (this.insertData().length) {
                    this.processingAddChild(newData[0], data.length - 1, newData[0][this.identificationProperty]);
                }
            }

            return this;
        },

        /**
         * Delete record instance
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         * @param {String|Number} recordId
         */
        deleteRecord: function (index, recordId) {
            this.updateInsertData(recordId);
            this._super();
        },

        /**
         * Updates insertData when record is deleted
         *
         * @param {String|Number} recordId
         */
        updateInsertData: function (recordId) {
            var data = this.getElementData(this.insertData(), recordId),
            prop = this.map[this.identificationDRProperty];

            this.insertData(_.reject(this.source.get(this.dataProvider), function (recordData) {
                return ~~recordData[prop] === ~~data[prop];
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

            property ? obj[property] = index : obj[this.map[this.identificationDRProperty]] = index;
            result = _.findWhere(array, obj);

            if (!result) {
                property ?
                    obj[property] = index.toString() :
                    obj[this.map[this.identificationDRProperty]] = index.toString();
            }

            result = _.findWhere(array, obj);

            return result;
        },

        /**
         * Processing pages before addChild
         *
         * @param {Object} ctx - element context
         * @param {Number|String} index - element index
         * @param {Number|String} prop - additional property to element
         */
        processingAddChild: function (ctx, index, prop) {
            if (this._elems.length > this.pageSize) {
                return false;
            }

            this.showSpinner(true);
            this.addChild(ctx, index, prop);
        },

        /**
         * Contains old data with new
         *
         * @param {Array} data
         *
         * @returns {Array} changed data
         */
        getNewData: function (data) {
            var changes = [],
                tmpObj = {};

            if (data.length !== this.relatedData.length) {
                _.each(data, function (obj) {
                    tmpObj[this.identificationDRProperty] = obj[this.identificationDRProperty];

                    if (!_.findWhere(this.relatedData, tmpObj)) {
                        changes.push(obj);
                    }
                }, this);
            }

            return changes;
        },

        /**
         * Processing insert data
         *
         * @param {Object} data
         */
        processingInsertData: function (data) {
            var changes,
                obj = {};

            changes = this._checkGridData(data);
            this.cacheGridData = data;

            if (changes.length) {
                obj[this.identificationDRProperty] = changes[0][this.map[this.identificationProperty]];

                if (_.findWhere(this.recordData(), obj)) {
                    return false;
                }

                changes.each(function (changedObject) {
                    this.mappingValue(changedObject);
                }, this);
            }
        },

        /**
         * Mapping value from grid
         *
         * @param {Array} data
         */
        mappingValue: function (data) {
            var obj = {},
                tmpObj = {};

            if (this.mappingSettings.enabled) {
                _.each(this.map, function (prop, index) {
                    obj[index] = !_.isUndefined(data[prop]) ? data[prop] : '';
                }, this);
            } else {
                obj = data;
            }

            if (this.mappingSettings.distinct) {
                tmpObj[this.identificationDRProperty] = obj[this.identificationDRProperty];

                if (_.findWhere(this.recordData(), tmpObj)) {
                    return false;
                }
            }

            if (!obj.hasOwnProperty(this.positionProvider)) {
                this.setMaxPosition();
                obj[this.positionProvider] = this.maxPosition;
            }

            this.source.set(this.dataScope + '.' + this.index + '.' + this.recordData().length, obj);
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
                obj[this.map[this.identificationDRProperty]] = record[this.map[this.identificationDRProperty]];

                if (!_.where(this.cacheGridData, obj).length) {
                    changes.push(data[index]);
                }
            }, this);

            return changes;
        }
    });
});
