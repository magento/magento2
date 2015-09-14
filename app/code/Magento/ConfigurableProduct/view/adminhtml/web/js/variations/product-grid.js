/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/core/app',
    'underscore',
    'notification'
], function (Component, $, bootstrap, _) {
    'use strict';

    return Component.extend({
        defaults: {
            productsGridUrl: null,
            productAttributes: [],
            productsModal: null,
            gridSelector: '[data-grid-id=associated-products-container]',
            modules: {
                productsFilter: '${ $.associatedProductsFilter }',
                productsProvider: '${ $.associatedProductsProvider }',
                productsMassAction: '${ $.associatedProductsMassAction }',
                variationsComponent: '${ $.configurableVariations }'
            }
        },

        /**
         * Initialize
         * @param options
         */
        initialize: function (options) {
            this._super(options);
            this.productsModal = $(this.gridSelector).modal({
                title: $.mage.__('Select Associated Product'),
                type: 'slide',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $.mage.__('Done'),
                        click: this.close.bind(this)
                    }
                ]
            });
            this._initGrid = _.once(this._initGrid);
        },

        /**
         * init Grid
         * @private
         */
        _initGrid: function (filterData) {
            $.ajax({
                type: 'GET',
                url: this._buildGridUrl(filterData),
                context: $('body')
            }).success(function (data) {
                bootstrap(JSON.parse(data));
            });
        },

        /**
         * Select different product in configurations section
         * @see configurable_associated_product_listing.xml
         * @param rowIndex
         */
        selectProduct: function (rowIndex) {
            this.productsMassAction().selected(this.getProductByIndex(rowIndex)['entity_id']);
            this.close();
        },

        /**
         * Open
         * @param filters
         * @param callbackName
         * @param showMassActionColumn
         */
        open: function (filterData, callbackName, showMassActionColumn) {
            this.callbackName = callbackName;
            this.productsMassAction(function (massActionComponent) {
                massActionComponent.visible(showMassActionColumn);
            });
            this._setFilter(filterData);
            this._initGrid(filterData);
            this._setSelected();
            this._showMessageAssociatedGrid();
            this.productsModal.trigger('openModal');
        },

        _setSelected: function () {
            this.variationsComponent(function (variation) {
                var entityIds = _.values(variation.productAttributesMap);
                this.productsMassAction(function (massActionComponent) {
                    massActionComponent.selected(entityIds);
                });
            }.bind(this));
        },

        /**
         * Close
         */
        close: function () {
            if (this.productsMassAction().selected().length) {
                this.variationsComponent()[this.callbackName](this.productsMassAction()
                    .selected.map(this.getProductById.bind(this)));
            }
            this.productsModal.trigger('closeModal');
        },

        /**
         * Get product by id
         * @param productId
         * @returns {*}
         */
        getProductById: function (productId) {
            return _.findWhere(this.productsProvider().data.items, {
                'entity_id': productId
            });
        },

        /**
         * Get product
         * @param rowIndex
         * @returns {*}
         */
        getProductByIndex: function (rowIndex) {
            return this.productsProvider().data.items[rowIndex];
        },

        /**
         * Build grid url
         * @private
         */
        _buildGridUrl: function (filterData) {
            var params = '?' + $.param({
                filters: filterData.filters,
                attributes_codes: this._getAttributesCodes(),
                filters_modifier: filterData.filters_modifier
            });

            return this.productsGridUrl + params;
        },

        /**
         * Get attributes codes
         * @private
         */
        _getAttributesCodes: function () {
            return this.variationsComponent().attributes.pluck('code');
        },

        /**
         * Show message associated grid
         * @private
         */
        _showMessageAssociatedGrid: function () {
            var messageInited = false;
            this.productsProvider(function (provider) {
                if (!messageInited) {
                    this.productsModal.notification();
                }
                this.productsModal.notification('clear');

                if (provider.data.items.length) {
                    this.productsModal.notification('add', {
                        message: $.mage.__(
                            'Choose a new product to delete and replace the current product configuration.'
                        ),
                        messageContainer: this.gridSelector
                    });
                } else {
                    this.productsModal.notification('add', {
                        message: $.mage.__('For better results, add attributes and attribute values to your products.'),
                        messageContainer: this.gridSelector
                    });
                }
            }.bind(this));
        },

        /**
         * Show manually grid
         */
        showManuallyGrid: function () {
            var filterModifier = _.mapObject(_.object(this._getAttributesCodes(), []), function () {
                return {'condition_type': 'notnull'};
            });

            this.open({filters_modifier: filterModifier}, 'rewriteProducts', true);
        },

        /**
         * Set filter
         * @private
         */
        _setFilter: function (filterData) {
            this.productsProvider(function (provider) {
                provider.params['filters_modifier'] = filterData.filters_modifier;
                provider.params['attributes_codes'] = this._getAttributesCodes();
            }.bind(this));

            this.productsFilter(function (filter) {
                filter.set('filters', filterData.filters).apply();
            });
        }
    });
});
