/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/components/insert-listing'
], function (_, insertListing) {
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
                modalWithGrid: '${ $.modalWithGrid }',
                productsFilters: '${ $.productsFilters }'
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

        /**
         * Initialize observables.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(
                'changeProductData'
            );

            return this;
        },

        /**
         * Get ids of used products.
         *
         * @returns {Array}
         */
        getUsedProductIds: function () {
            var usedProductsIds = this.source.get(this.dataScopeAssociatedProduct);

            return usedProductsIds.slice();
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
                this.productsFilters().clear();
                this._setFilters(this.externalProviderParams);
                this._setVisibilityMassActionColumn();
            }

            return this.render();
        },

        /**
         * Show grid with assigned product.
         *
         * @returns {Object}
         */
        showGridAssignProduct: function () {
            this.product = {};
            this.rowIndexForChange = undefined;

            return this.doRender(true, 'assignProduct');
        },

        /**
         * Show grid with changed product.
         *
         * @param {String} rowIndex
         * @param {String} product
         */
        showGridChangeProduct: function (rowIndex, product) {
            this.rowIndexForChange = rowIndex;
            this.product = product;
            this.doRender(false, 'changeProduct');
        },

        /**
         * Select product.
         *
         * @param {String} rowIndex
         */
        selectProduct: function (rowIndex) {
            this.changeProductData({
                rowIndex: this.rowIndexForChange,
                product: this.productsProvider().data.items[rowIndex]
            });
            this.modalWithGrid().closeModal();
        },

        /**
         * Set visibility state for mass action column
         *
         * @private
         */
        _setVisibilityMassActionColumn: function () {
            this.productsMassAction(function (massActionComponent) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = this.showMassActionColumn;
                }, this);
                massActionComponent.visible = this.showMassActionColumn;
            }.bind(this));
        },

        /**
         * Set filters.
         *
         * @param {Object} params
         * @private
         */
        _setFilters: function (params) {
            var filterModifier = {},
                attrCodes,
                usedProductIds,
                attributes;

            params = _.omit(params);

            if (!this.paramsUpdated) {
                this.gridInitialized = true;
                this.paramsUpdated = true;

                attrCodes = this._getAttributesCodes();
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

                if (this.typeGrid === 'changeProduct') {
                    attributes = JSON.parse(this.product.attributes);

                    filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
                        return {
                            'condition_type': 'eq',
                            'value': value
                        };
                    }));

                    params.filters = attributes;
                } else {
                    params.filters = {};
                }

                params['attributes_codes'] = attrCodes;

                this.set('externalProviderParams', params);
                this.set('externalFiltersModifier', filterModifier);
            }
        },

        /**
         * Get attribute codes.
         *
         * @returns {Array}
         * @private
         */
        _getAttributesCodes: function () {
            var attrCodes = this.source.get('data.attribute_codes');

            return attrCodes ? attrCodes : [];
        },

        /**
         * Get product variations.
         *
         * @returns {Array}
         * @private
         */
        _getProductVariations: function () {
            var matrix = this.source.get('data.configurable-matrix');

            return matrix ? matrix : [];
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length && this.typeGrid === 'assignProduct') {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                this._disableRows(data.items);
            }
        },

        /**
         * Handle manual selection.
         *
         * @param {Array} selected
         * @private
         */
        _handleManualGridSelect: function (selected) {
            var selectedRows,
                selectedVariationKeys;

            if (this.typeGrid === 'assignProduct') {
                selectedRows = _.filter(this.productsProvider().data.items, function (row) {
                    return selected.indexOf(row['entity_id']) !== -1;
                });
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
                            return configurableVariationKeys.indexOf(variationKey) !== -1;
                        }
                    ));

                massaction.disabled(_.difference(rowsForDisable, selected));
            }.bind(this));
        },

        /**
         * Get variation key map used in manual grid.
         *
         * @param {Array} items
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
