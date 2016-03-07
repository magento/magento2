/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, registry, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            actionsListOpened: false,
            canEditField: 'canEdit',
            newProductField: 'newProduct',
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            dataProviderFromGrid: '',
            dataProviderChangeFromGrid: '',
            insertDataFromGrid: [],
            changeDataFromGrid: [],
            dataProviderFromWizard: '',
            insertDataFromWizard: [],
            map: null,
            isEmpty: true,
            cacheGridData: [],
            unionInsertData: [],
            deleteProperty: false,
            dataLength: 0,
            identificationProperty: 'id',
            'attribute_set_id': '',
            listens: {
                'insertDataFromGrid': 'processingInsertDataFromGrid',
                'insertDataFromWizard': 'processingInsertDataFromWizard',
                'unionInsertData': 'processingUnionInsertData',
                'changeDataFromGrid': 'processingChangeDataFromGrid'
            },
            imports: {
                'attribute_set_id': '${$.provider}:data.product.attribute_set_id'
            },
            'exports': {
                'attribute_set_id': '${$.provider}:data.new-variations-attribute-set-id'
            },
            modules: {
                modalWithGrid: '${ $.modalWithGrid }',
                gridWithProducts: '${ $.gridWithProducts}'
            }
        },

        /**
         * Open modal with grid.
         * 
         * @param {String} rowIndex
         */
        openModalWithGrid: function (rowIndex) {
            var productSource = this.source.get(this.dataScope + '.' + this.index + '.' + rowIndex);
            var product = {
                'id': productSource.id,
                'attributes': productSource.configurable_attribute
            };

            this.modalWithGrid().openModal();
            this.gridWithProducts().showGridChangeProduct(rowIndex, product);
        },

        /**
         * Initialize children
         *
         * @returns {Object} Chainable.
         */
        initChildren: function () {
            var tmpArray = [];

            this.recordData.each(function (recordData) {
                tmpArray.push(recordData);
            }, this);

            this.unionInsertData(tmpArray);

            return this;
        },

        /**
         * Delete record
         *
         * @param {Number} index - row index
         */
        deleteRecord: function (index) {
            var tmpArray;

            this.reRender = false;
            tmpArray = this.unionInsertData();
            tmpArray.splice(index, 1);

            this.unionInsertData(tmpArray);
            this.reRender = true;
        },

        /**
         * Generate associated products
         */
        generateAssociatedProducts: function () {
            var productsIds = [];

            this.unionInsertData().each(function (data) {
                if (data.id !== null) {
                    productsIds.push(data.id);
                }
            });

            this.source.set(this.dataScopeAssociatedProduct, productsIds);
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertDataFromGrid', 'unionInsertData', 'isEmpty', 'actionsListOpened'
                ]);

            return this;
        },

        /**
         * Process union insert data.
         *
         * @param {Array} data
         */
        processingUnionInsertData: function (data) {
            var dataInc = 0,
                diff = 0,
                dataCount,
                elemsCount,
                lastRecord;

            this.source.remove(this.dataScope + '.' + this.index);
            this.isEmpty(data.length === 0);

            _.each(data, function (row) {
                _.each(row, function (value, key) {
                    var path = this.dataScope + '.' + this.index + '.' + dataInc + '.' + key;

                    this.source.set(path, value);
                }, this);

                ++dataInc;
            }, this);

            // Render
            dataCount = data.length;
            elemsCount = this.elems().length;

            if (dataCount > elemsCount) {
                for (diff = dataCount - elemsCount; diff > 0; diff--) {
                    this.addChild(data, false);
                }
            } else {
                for (diff = elemsCount - dataCount; diff > 0; diff--) {
                    lastRecord =
                        _.findWhere(this.elems(), {
                            index: this.recordIterator - 1
                        }) ||
                        _.findWhere(this.elems(), {
                            index: (this.recordIterator - 1).toString()
                        });
                    lastRecord.destroy();
                    --this.recordIterator;
                }
            }

            this.generateAssociatedProducts();
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertDataFromGrid: function (data) {
            var changes,
                tmpArray;

            if (!data.length) {
                return;
            }

            tmpArray = this.unionInsertData();

            changes = this._checkGridData(data);
            this.cacheGridData = data;

            changes.each(function (changedObject) {
                var mappedData = this.mappingValue(changedObject);

                mappedData[this.canEditField] = 0;
                mappedData[this.newProductField] = 0;
                mappedData.variationKey = this._getVariationKey(changedObject);
                mappedData['configurable_attribute'] = this._getConfigurableAttribute(changedObject);
                tmpArray.push(mappedData);
            }, this);

            this.unionInsertData(tmpArray);
        },

        /**
         * Process changes from grid.
         * 
         * @param {Object} data
         */
        processingChangeDataFromGrid: function (data) {
            var tmpArray = this.unionInsertData(),
                mappedData = this.mappingValue(data.product);

            mappedData[this.canEditField] = 0;
            mappedData[this.newProductField] = 0;
            mappedData['variationKey'] = this._getVariationKey(data.product);
            mappedData['configurable_attribute'] = this._getConfigurableAttribute(data.product);

            tmpArray[data.rowIndex] = mappedData;

            this.unionInsertData(tmpArray);
        },

        /**
         * Get variation key.
         *
         * @param {Object} data
         * @returns {String}
         * @private
         */
        _getVariationKey: function (data) {
            var attrCodes = this.source.get('data.attribute_codes'),
                key = [];

            attrCodes.each(function (code) {
                key.push(data[code]);
            });

            return key.sort().join('-');
        },

        /**
         * Get configurable attribute.
         * 
         * @param {Object} data
         * @returns {String}
         * @private
         */
        _getConfigurableAttribute: function (data) {
            var attrCodes = this.source.get('data.attribute_codes'),
                confAttrs = {};

            attrCodes.each(function (code) {
                confAttrs[code] = data[code];
            });

            return JSON.stringify(confAttrs);
        },

        /**
         * Process data insertion from wizard
         *
         * @param {Object} data
         */
        processingInsertDataFromWizard: function (data) {
            var tmpArray = this.unionInsertData(),
                productIdsToDelete = this.source.get(this.dataScopeAssociatedProduct),
                index,
                product = {};

            tmpArray = this.unsetArrayItem(
                tmpArray,
                {
                    id: null
                }
            );

            _.each(data, function (row) {
                if (row.productId) {
                    index = _.indexOf(productIdsToDelete, row.productId);

                    if (index > -1) {
                        productIdsToDelete.splice(index, 1);
                        tmpArray = this.unsetArrayItem(
                            tmpArray,
                            {
                                id: row.productId
                            }
                        );
                    }
                }

                product = {
                    'id': row.productId,
                    'product_link': row.productUrl,
                    'name': row.name,
                    'sku': row.sku,
                    'status': row.status,
                    'price': row.price,
                    'price_currency': row.priceCurrency,
                    'price_string': row.priceCurrency + row.price,
                    'weight': row.weight,
                    'qty': row.quantity,
                    'variationKey': row.variationKey,
                    'configurable_attribute': row.attribute,
                    'thumbnail_image': row.images.preview,
                    'media_gallery': row['media_gallery'],
                    'swatch_image': row['swatch_image'],
                    'small_image': row['small_image'],
                    'thumbnail': row.thumbnail
                };
                product[this.canEditField] = row.editable;
                product[this.newProductField] = row.newProduct;

                tmpArray.push(product);
            }, this);

            _.each(productIdsToDelete, function (id) {
                tmpArray = this.unsetArrayItem(
                    tmpArray,
                    {
                        id: id
                    }
                );
            }, this);

            this.unionInsertData(tmpArray);
        },

        /**
         * Remove array items matching condition.
         *
         * @param {Array} data
         * @param {Object} condition
         * @returns {Array}
         */
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
        },

        /**
         * Toggle actions list.
         *
         * @param {Number} rowIndex
         * @returns {Object} Chainable.
         */
        toggleActionsList: function (rowIndex) {
            var state = false;

            if (rowIndex !== this.actionsListOpened()) {
                state = rowIndex;
            }
            this.actionsListOpened(state);

            return this;
        },

        /**
         * Close action list.
         *
         * @param {Number} rowIndex
         * @returns {Object} Chainable
         */
        closeList: function (rowIndex) {
            if (this.actionsListOpened() === rowIndex) {
                this.actionsListOpened(false);
            }

            return this;
        },

        /**
         * Toggle product status.
         *
         * @param {Number} rowIndex
         */
        toggleStatusProduct: function (rowIndex) {
            var tmpArray = this.unionInsertData(),
                status = tmpArray[rowIndex].status;

            if (status === 1) {
                tmpArray[rowIndex].status = 2;
            } else {
                tmpArray[rowIndex].status = 1;
            }

            this.unionInsertData(tmpArray);
        }
    });
});
