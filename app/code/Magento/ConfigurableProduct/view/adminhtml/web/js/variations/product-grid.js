// jscs:disable requireDotNotation
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
            button: '',
            gridSelector: '[data-grid-id=associated-products-container]',
            modules: {
                productsFilter: '${ $.associatedProductsFilter }',
                productsProvider: '${ $.associatedProductsProvider }',
                productsMassAction: '${ $.associatedProductsMassAction }',
                variationsComponent: '${ $.configurableVariations }'
            },
            listens: {
                '${ $.associatedProductsProvider }:data': '_showMessageAssociatedGrid',
                '${ $.configurableVariations }:productMatrix': '_showButtonAddManual'
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
                        click: this.close.bind(this, null)
                    }
                ]
            });

            this.productsProvider(function () {
                this.productsModal.notification();
            }.bind(this));
            this.variationsComponent(function (variation) {
                this._showButtonAddManual(variation.productMatrix());
            }.bind(this));

            this._initGrid = _.once(this._initGrid);
        },

        /**
         * Initial observerable
         * @returns {*}
         */
        initObservable: function () {
            this._super().observe('button');

            return this;
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
            this.close(rowIndex);
        },

        /**
         * Open
         * @param function filterData
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
            this.productsModal.trigger('openModal');
        },

        /**
         * Close
         */
        close: function (rowIndex) {
            try {
                if (this.productsMassAction().selected().length) {
                    this.variationsComponent()[this.callbackName](this.productsMassAction()
                        .selected.map(this.getProductById.bind(this)));
                    this.productsMassAction().selected([]);
                } else if (!_.isNull(rowIndex)) {
                    this.variationsComponent()[this.callbackName]([this.getProductByIndex(rowIndex)]);
                }
                this.productsModal.trigger('closeModal');
            } catch (e) {
                if (e.name === 'UserException') {
                    this.productsModal.notification('clear');
                    this.productsModal.notification('add', {
                        message: e.message,
                        messageContainer: this.gridSelector
                    });
                } else {
                    throw e;
                }
            }
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
                'filters': filterData.filters,
                'attributes_codes': this._getAttributesCodes(),
                'filters_modifier': filterData['filters_modifier']
            });

            return this.productsGridUrl + params;
        },

        _showButtonAddManual: function (variations) {
            return this.button(variations.length);
        },

        /**
         * Get attributes codes
         * @private
         */
        _getAttributesCodes: function () {
            return this.variationsComponent().attributes.pluck('code');
        },

        /**
         * Show data associated grid
         * @private
         */
        _showMessageAssociatedGrid: function (data) {
            this.productsModal.notification('clear');

            if (data.items.length) {
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
        },

        /**
         * Show manually grid
         */
        showManuallyGrid: function () {
            var filterModifier = _.mapObject(_.object(this._getAttributesCodes(), []), function () {
                    return {
                        'condition_type': 'notnull'
                    };
                }),
                usedProductIds = _.values(this.variationsComponent().productAttributesMap);

            if (usedProductIds) {
                filterModifier['entity_id'] = {
                    'condition_type': 'nin', value: usedProductIds
                };
            }

            this.open({
                'filters_modifier': filterModifier
            }, 'appendProducts', true);
        },

        /**
         * Set filter
         * @private
         */
        _setFilter: function (filterData) {
            this.productsProvider(function (provider) {
                provider.params['filters_modifier'] = filterData['filters_modifier'];
                provider.params['attributes_codes'] = this._getAttributesCodes();
            }.bind(this));

            this.productsFilter(function (filter) {
                filter.set('filters', _.extend({
                    'filters_modifier': filterData['filters_modifier']
                }, filterData.filters))
                    .apply();
            });
        }
    });
});
