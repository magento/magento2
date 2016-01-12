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
            insertData: null,
            map: null,
            temporaryData: null,
            cacheGridData: [],
            listens: {
                'insertData': 'processingInsertData',
                'elems': 'mappingValue'
            }
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
                    'insertData'
                ]);

            return this;
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

            changes.each(function () {
                this.addChild();
            }, this);
        },

        /**
         * Delete record instance,
         * call parent method deleteRecord,
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         */
        deleteRecord: function (index) {
            var dataObj,
                rowInstance = _.find(this.elems(), function (elem) {
                return elem.index === index;
            });

            this.notTriggered = true;
            this._super();
            this.notTriggered = false;
            dataObj = {
                name: this.recordData()[rowInstance.index].name
            };
            this.insertData(_.reject(this.source[this.dataScope][this.dataProvider], dataObj));
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
                renderedElement;

            max.each(function (record, index) {
                renderedElement = _.where(this.cacheGridData, data[index]);

                if (!renderedElement.length) {
                    changes.push(data[index]);
                }
            }, this);

            return changes;
        },

        /**
         * Mapped value
         *
         * @param {Array} data - array with records data
         */
        mappingValue: function (data) {
            var path;

            if (this.notTriggered) {
                return false;
            }

            data.each(function (record, id) {
                _.each(this.map, function (prop, index) {
                    path = this.dataScope + '.' + this.index + '.' + data[id].index + '.' + index;
                    this.source.set(path, this.insertData()[id][prop]);
                }, this);
            }, this);
        }
    });
});
