/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-listing'
], function (insertListing) {
    'use strict';

    return insertListing.extend({
        defaults: {
            gridInitialized: false,
            paramsUpdated: false,
            showMassActionColumn: true,
            currentProductId: 0,
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            typeGrid: '',
            product: {},
            rowIndexForChange: undefined,
            changeProductData: [],
            modules: {
                productsProvider: '${ $.productsProvider }',
                productsColumns: '${ $.productsColumns }',
                productsMassAction: '${ $.productsMassAction }',
                modalWithGrid: '${ $.modalWithGrid }'
            },
            exports: {
                externalProviderParams: '${ $.externalProvider }:params'
            },
            links: {
                changeProductData: '${ $.provider }:${ $.changeProductProvider }'
            },
            listens: {
                '${ $.externalProvider }:params': '_setFilters _setVisibilityMassActionColumn',
                '${ $.productsProvider }:data': '_handleManualGridOpening',
                '${ $.productsMassAction }:selected': '_handleManualGridSelect'
            }
        },

        initObservable: function () {
            this._super().observe(
                'changeProductData'
            );

            return this;
        },

        getUsedProductIds: function () {
            return this.source.get(this.dataScopeAssociatedProduct);
        },

        /**
         * Request for render content.
         *
         * @returns {Object}
         */
        doRender: function (showMassActionColumn, typeGrid) {
            this.typeGrid = typeGrid;
            this.showMassActionColumn = showMassActionColumn;
            if (this.gridInitialized) {
                this.paramsUpdated = false;
                this._setFilters(this.externalProviderParams);
                this._setVisibilityMassActionColumn();
            }

            return this.render();
        },

        showGridAssignProduct: function () {
            this.product = {};
            this.rowIndexForChange = undefined;
            return this.doRender(true, 'assignProduct');
        },

        showGridChangeProduct: function (rowIndex, product) {
            this.rowIndexForChange = rowIndex;
            this.product = product;
            this.doRender(false, 'changeProduct');
        },

        selectProduct: function (rowIndex) {
            this.changeProductData({
                rowIndex: this.rowIndexForChange,
                product: this.productsProvider().data.items[rowIndex]
            });
            this.modalWithGrid().closeModal();
        },

        _setVisibilityMassActionColumn: function () {
            this.productsMassAction(function (massActionComponent) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = this.showMassActionColumn;
                }, this);
                massActionComponent.visible = this.showMassActionColumn;
            }.bind(this));
        },

        _setFilters: function (params) {
            if (!this.paramsUpdated) {
                this.gridInitialized = true;
                this.paramsUpdated = true;

                var filterModifier = {},
                    attrCodes = this._getAttributesCodes(),
                    usedProductIds = this.getUsedProductIds();

                if (this.currentProductId) {
                    usedProductIds.push(this.currentProductId);
                }

                filterModifier['entity_id'] = {
                    'condition_type': 'nin', value: usedProductIds
                };
                attrCodes.each(function (code) {
                    filterModifier[code] = {
                        'condition_type': 'notnull'
                    };
                });

                if (this.typeGrid == 'changeProduct') {
                    var attributes = JSON.parse(this.product.attributes);

                    filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
                        return {
                            'condition_type': 'eq',
                            'value': value
                        };
                    }));

                    params['filters'] = attributes;
                }


                params['attributes_codes'] = attrCodes;

                this.set('externalProviderParams', params);
                this.set('externalFiltersModifier', filterModifier);
            }
        },

        _getAttributesCodes: function () {
            var attrCodes = this.source.get('data.attribute_codes');

            return attrCodes ? attrCodes : [];
        },

        _getProductVariations: function () {
            var matrix = this.source.get('data.configurable-matrix');

            return matrix ? matrix : [];
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length && this.typeGrid == 'assignProduct') {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                this._disableRows(data.items);
            }
        },

        /**
         * @private
         */
        _handleManualGridSelect: function (selected) {
            if (this.typeGrid == 'assignProduct') {
                var selectedRows = _.filter(this.productsProvider().data.items, function (row) {
                        return selected.indexOf(row['entity_id']) != -1;
                    }),
                    selectedVariationKeys = _.values(this._getVariationKeyMap(selectedRows));
                this._disableRows(this.productsProvider().data.items, selectedVariationKeys, selected);
            }
        },

        /**
         * Disable rows in grid for products with the same variation key
         *
         * @param {Array} items
         * @param {Array} selectedVariationKeys
         * @param {Array} selected
         * @private
         */
        _disableRows: function (items, selectedVariationKeys, selected) {
            selectedVariationKeys = selectedVariationKeys === undefined ? [] : selectedVariationKeys;
            selected = selected === undefined ? [] : selected;
            this.productsMassAction(function (massaction) {
                var configurableVariationKeys = _.union(
                    selectedVariationKeys,
                    _.pluck(this._getProductVariations(), 'variationKey')
                    ),
                    variationKeyMap = this._getVariationKeyMap(items),
                    rowsForDisable = _.keys(_.pick(
                        variationKeyMap,
                        function (variationKey) {
                            return configurableVariationKeys.indexOf(variationKey) != -1;
                        }
                    ));

                massaction.disabled(_.difference(rowsForDisable, selected));
            }.bind(this));
        },

        /**
         * Get variation key map used in manual grid.
         *
         * @param items
         * @returns {Array} [{entity_id: variation-key}, ...]
         * @private
         */
        _getVariationKeyMap: function (items) {
            var variationKeyMap = {};

            _.each(items, function (row) {
                variationKeyMap[row['entity_id']] = _.values(
                    _.pick(row, this._getAttributesCodes())
                ).sort().join('-');

            }, this);

            return variationKeyMap;
        }
    });
});