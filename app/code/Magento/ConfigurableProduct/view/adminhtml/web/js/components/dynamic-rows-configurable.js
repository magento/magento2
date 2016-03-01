/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            canEditField: 'canEdit',
            newProductField: 'newProduct',
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            associatedProductIncrement: 0,
            dataProviderFromGrid: '',
            insertDataFromGrid: [],
            dataProviderFromWizard: '',
            insertDataFromWizard: [],
            map: null,
            isEmpty: true,
            cacheGridData: [],
            unionInsertData: [],
            deleteProperty: false,
            dataLength: 0,
            identificationProperty: 'id',
            attribute_set_id: '',
            listens: {
                'insertDataFromGrid': 'processingInsertDataFromGrid',
                'insertDataFromWizard': 'processingInsertDataFromWizard',
                'unionInsertData': 'processingUnionInsertData'
            },
            imports: {
                'attribute_set_id': '${$.provider}:data.product.attribute_set_id'
            },
            'exports': {
                'attribute_set_id': '${$.provider}:data.new-variations-attribute-set-id'
            }
        },

        /**
         * Initialize children
         */
        initChildren: function () {
            var tmpArray = [];

            this.recordData.each(function (recordData) {
                tmpArray.push(recordData);
                this.addAssociatedProduct(recordData.id);
            }, this);

            this.unionInsertData(tmpArray);

            return this;
        },

        addAssociatedProduct: function (productId) {
            this.source.set(this.dataScopeAssociatedProduct + '.' + this.associatedProductIncrement, productId);
            ++this.associatedProductIncrement;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertDataFromGrid', 'unionInsertData'
                ]);

            return this;
        },

        processingUnionInsertData: function (data) {
            this.clear();
            this.source.set(this.dataScope + '.' + this.index, []);

            _.each(data, function (row) {
                _.each(row, function (value, key) {
                    var path = this.dataScope + '.' + this.index + '.' + this.recordIterator + '.' + key;
                    this.source.set(path, value);
                }, this);

                this.addChild(data, false);
            }, this);

            //this.set('isEmpty', true);
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertDataFromGrid: function (data) {
            var changes;

            if (!data.length) {
                return;
            }

            var tmpArray = this.unionInsertData();

            changes = this._checkGridData(data);
            this.cacheGridData = data;

            changes.each(function (changedObject) {
                this.addAssociatedProduct(changedObject[this.map.id]);

                var mappedData = this.mappingValue(changedObject);
                mappedData[this.canEditField] = 0;
                mappedData[this.newProductField] = 0;
                tmpArray.push(mappedData);
            }, this);

            this.unionInsertData(tmpArray);
        },

        processingInsertDataFromWizard: function (data) {
            var tmpArray = this.unionInsertData();
            tmpArray = this.unsetArrayItem(tmpArray, {'id': null});

            _.each(data, function (row) {
                var product = {
                    'id': row.productId,
                    'product_link': row.productUrl,
                    'name': row.name,
                    'sku': row.sku,
                    'status': row.status,
                    'price': row.price,
                    'price_currency': row.priceCurrency,
                    'price_string': row.priceCurrency + row.price,
                    'weight': row.weight,
                    'quantity_and_stock_status.qty': row.quantity,
                    'variationKey': row.variationKey,
                    'configurable_attribute': row.attribute
                };
                product[this.canEditField] = 1;
                product[this.newProductField] = 1;

                tmpArray.push(product);
            }, this);

            this.unionInsertData(tmpArray);
        },

        unsetArrayItem: function (data, condition) {
            var objs = _.where(data, condition);

            _.each(objs, function (obj) {
                var index = _.indexOf(data, obj);
                if (index > -1) {
                    data.splice(index, 1);
                }
            });

            return data;
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
        mappingValue: function (data) {
            var result = {};
            _.each(this.map, function (prop, index) {
                result[index] = data[prop];
            });

            return result;
        }
    });
});
