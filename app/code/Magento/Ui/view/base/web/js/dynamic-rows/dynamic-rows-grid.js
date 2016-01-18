/**l
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
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertData: function (data) {
            var changes = this._checkGridData(data);

            this.cacheGridData = data;

            changes.each(function (changedObject) {
                this.addChild(changedObject, false, parseInt(changedObject[this.map.id], 10));
            }, this);
        },

        /**
         * Delete record instance,
         * call parent method deleteRecord,
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         */
        deleteRecord: function (index, recordId) {
            var data = this.getElementData(this.insertData(), recordId),
                recordData = this.getElementData(this.recordData(), recordId, 'record_id');

            this._super();
            this.insertData(_.reject(this.source.get(this.dataProvider), data));
            this.recordData(_.reject(this.source.get(this.dataScope), recordData));

            if (this.elems().length) {
                this.sort(this.elems()[0].position, this.elems()[0]);
            }
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

            this._super(data, index);

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
                changes = [];

            max.each(function (record, index) {
                if (!_.where(this.cacheGridData, data[index]).length) {
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
            var obj = {};

            index = index.toString();
            property ? obj[property] = index : obj[this.map.id] = index;

            return _.findWhere(array, obj);
        }
    });
});
