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
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            modules: {
                productsProvider: '${ $.productsProvider }',
                productsColumns: '${ $.productsColumns }',
                productsMassAction: '${ $.productsMassAction }'
            },
            listens: {
                '${ $.externalProvider }:params': 'setFilters',
                //'${ $.productsProvider }:data': '_handleManualGridOpening'
            }
        },

        getUsedProductIds: function () {
            return this.source.get(this.dataScopeAssociatedProduct);
        },

        /**
         * Request for render content.
         *
         * @returns {Object}
         */
        render: function (params) {
            if (this.gridInitialized) {
                this.paramsUpdated = false;
                this.setFilters();
            }

            return this._super();
        },

        setFilters: function (filters) {
            if (!this.paramsUpdated) {
                this.gridInitialized = true;
                this.paramsUpdated = true;

                var filter = {};

                filter['entity_id'] = {
                    'condition_type': 'nin', value: this.getUsedProductIds()
                };

                this.set('externalFiltersModifier', filter);
            }
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                //this._disableRows(data.items);
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
                    _.pluck(this.variationsComponent().productMatrix(), 'variationKey')
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
        }
    });
});