/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'jquery'
], function (_, registry, dynamicRows, $) {
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
            isShowAddProductButton: false,
            cacheGridData: [],
            unionInsertData: [],
            deleteProperty: false,
            dataLength: 0,
            identificationProperty: 'id',
            'attribute_set_id': '',
            attributesTmp: [],
            changedFlag: 'was_changed',
            listens: {
                'insertDataFromGrid': 'processingInsertDataFromGrid',
                'insertDataFromWizard': 'processingInsertDataFromWizard',
                'unionInsertData': 'processingUnionInsertData',
                'changeDataFromGrid': 'processingChangeDataFromGrid',
                'isEmpty': 'changeVisibility'
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
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super()
                .changeVisibility(this.isEmpty());

            return this;
        },

        /**
         * Change visibility
         *
         * When isEmpty = true, then visbible = false
         *
         * @param {Boolean} isEmpty
         */
        changeVisibility: function (isEmpty) {
            this.visible(!isEmpty);
        },

        /**
         * Open modal with grid.
         *
         * @param {String} rowIndex
         */
        openModalWithGrid: function (rowIndex) {
            var productSource = this.source.get(this.dataScope + '.' + this.index + '.' + rowIndex),
                product = {
                    'id': productSource.id,
                    'attributes': productSource['configurable_attribute']
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
            var tmpArray,
                lastRecord;

            this.reRender = false;
            tmpArray = this.getUnionInsertData();
            tmpArray.splice(index, 1);

            if (!tmpArray.length) {
                this.attributesTmp = this.source.get('data.attributes');
                this.source.set('data.attributes', []);
                this.cacheGridData = [];
            }

            if (parseInt(this.currentPage(), 10) === this.pages()) {
                lastRecord =
                    _.findWhere(this.elems(), {
                        index: this.startIndex + this.getChildItems().length - 1
                    }) ||
                    _.findWhere(this.elems(), {
                        index: (this.startIndex + this.getChildItems().length - 1).toString()
                    });

                lastRecord.destroy();
            }

            this.unionInsertData(tmpArray);

            if (this.pages() < parseInt(this.currentPage(), 10)) {
                this.currentPage(this.pages());
            }

            this.reRender = true;
            this.showSpinner(false);
        },

        /**
         * Generate associated products
         */
        generateAssociatedProducts: function () {
            var productsIds = [];

            this.getUnionInsertData().forEach(function (data) {
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
                    'insertDataFromGrid', 'unionInsertData', 'isEmpty', 'isShowAddProductButton', 'actionsListOpened'
                ]);

            return this;
        },

        /**
         * Get union insert data from source
         *
         * @returns {Array}
         */
        getUnionInsertData: function () {
            var source = this.source.get(this.dataScope + '.' + this.index),
                result = [];

            _.each(source, function (data) {
                result.push(data);
            });

            return result;
        },

        /**
         * Process union insert data.
         *
         * @param {Array} data
         */
        processingUnionInsertData: function (data) {
            var dataCount,
                elemsCount,
                tmpData,
                path,
                attributeCodes = this.source.get('data.attribute_codes');

            this.isEmpty(data.length === 0);
            this.isShowAddProductButton(
                (!attributeCodes || data.length > 0 ? data.length : attributeCodes.length) > 0
            );

            tmpData = data.slice(this.pageSize * (this.currentPage() - 1),
                                 this.pageSize * (this.currentPage() - 1) + parseInt(this.pageSize, 10));

            this.source.set(this.dataScope + '.' + this.index, []);

            _.each(tmpData, function (row, index) {
                path = this.dataScope + '.' + this.index + '.' + (this.startIndex + index);
                row.attributes = $('<i></i>').text(row.attributes).html();
                row.sku = $('<i></i>').text(row.sku).html();
                this.source.set(path, row);
            }, this);

            this.source.set(this.dataScope + '.' + this.index, data);
            this.parsePagesData(data);

            // Render
            dataCount = data.length;
            elemsCount = this.elems().length;

            if (dataCount > elemsCount) {
                this.getChildItems().each(function (elemData, index) {
                    this.addChild(elemData, this.startIndex + index);
                }, this);
            } else {
                for (elemsCount; elemsCount > dataCount; elemsCount--) {
                    this.elems()[elemsCount - 1].destroy();
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

            tmpArray = this.getUnionInsertData();

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

            // Attributes cannot be changed before regeneration thought wizard
            if (!this.source.get('data.attributes').length) {
                this.source.set('data.attributes', this.attributesTmp);
            }
            this.unionInsertData(tmpArray);
        },

        /**
         * Process changes from grid.
         *
         * @param {Object} data
         */
        processingChangeDataFromGrid: function (data) {
            var tmpArray = this.getUnionInsertData(),
                mappedData = this.mappingValue(data.product);

            mappedData[this.canEditField] = 0;
            mappedData[this.newProductField] = 0;
            mappedData.variationKey = this._getVariationKey(data.product);
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
            var tmpArray = this.getUnionInsertData(),
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
                product = this.getProductData(row);

                product[this.changedFlag] = true;
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
         *
         * @param {Object} row
         * @returns {Object}
         */
        getProductData: function (row) {
            var product,
                attributesText = '';

            _.each(row.options, function (attribute) {
                if (attributesText) {
                    attributesText += ', ';
                }
                attributesText += attribute['attribute_label'] + ': ' + attribute.label;
            }, this);

            product = {
                'id': row.productId,
                'product_link': row.productUrl,
                'name': $('<i></i>').text(row.name).html(),
                'sku': $('<i></i>').text(row.sku).html(),
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
                image: row.image,
                'thumbnail': row.thumbnail,
                'attributes': attributesText
            };

            return product;
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
            var tmpArray = this.getUnionInsertData(),
                status = parseInt(tmpArray[rowIndex].status, 10);

            if (status === 1) {
                tmpArray[rowIndex].status = 2;
            } else {
                tmpArray[rowIndex].status = 1;
            }

            tmpArray[rowIndex][this.changedFlag] = true;
            this.unionInsertData(tmpArray);
        }
    });
});
