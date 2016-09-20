// jscs:disable requireDotNotation
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/core/app',
    'underscore',
    'notification',
    'mage/translate'
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
                productsFilter: '${ $.productsFilter }',
                productsProvider: '${ $.productsProvider }',
                productsMassAction: '${ $.productsMassAction }',
                productsColumns: '${ $.productsColumns }',
                variationsComponent: '${ $.configurableVariations }'
            },
            listens: {
                '${ $.productsProvider }:data': '_showMessageAssociatedGrid _handleManualGridOpening',
                '${ $.productsMassAction }:selected': '_handleManualGridSelect',
                '${ $.configurableVariations }:productMatrix': '_showButtonAddManual _switchProductType'
            }
        },

        /**
         * Initialize
         * @param {Array} options
         */
        initialize: function (options) {
            this._super(options);
            this.productsModal = $(this.gridSelector).modal({
                title: $.mage.__('Select Associated Product'),
                type: 'slide',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),

                        /**
                         * Close modal
                         * @event
                         */
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
            this._switchProductType = _.wrap(this._switchProductType.bind(this), function (func, params) {
                if (!!params.length !== !!this.init) {
                    this.init = !!params.length;
                    func(params);
                }
            }.bind(this._switchProductType));
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
         * @param {Integer} rowIndex
         */
        selectProduct: function (rowIndex) {
            this.close(rowIndex);
        },

        /**
         * Open
         * @param {Object} filterData - filter data
         * @param {Object|*} filterData.filters - attribute name
         * @param {Object|*} filterData.filters_modifier - modifier value
         * @param {String} callbackName
         * @param {Boolean} showMassActionColumn
         */
        open: function (filterData, callbackName, showMassActionColumn) {
            this.callbackName = callbackName;
            this.productsMassAction(function (massActionComponent) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = showMassActionColumn;
                });
                massActionComponent.visible = showMassActionColumn;
            }.bind(this));
            this._setFilter(filterData);
            this._initGrid(filterData);
            this.productsModal.trigger('openModal');
        },

        /**
         * Close
         */
        close: function (rowIndex) {
            try {
                if (this.productsMassAction().selected.getLength()) {
                    this.variationsComponent()[this.callbackName](this.productsMassAction()
                        .selected.map(this.getProductById.bind(this)));
                    this.productsMassAction().deselectAll();
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
         * @param {Integer} productId
         * @returns {*}
         */
        getProductById: function (productId) {
            return _.findWhere(this.productsProvider().data.items, {
                'entity_id': productId
            });
        },

        /**
         * Get product
         * @param {Integer} rowIndex
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

        /**
         * Show button add manual
         * @param {Array} variations
         * @returns {*}
         * @private
         */
        _showButtonAddManual: function (variations) {
            return this.button(variations.length);
        },

        _switchProductType: function (variations) {
            $(document).trigger('changeConfigurableTypeProduct', variations.length);
        },

        /**
         * Get attributes codes used for configurable
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
                    message: $.mage.__('Choose a new product to delete and replace the current product configuration.'),
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

            if (usedProductIds && usedProductIds.length > 0) {
                filterModifier['entity_id'] = {
                    'condition_type': 'nin', value: usedProductIds
                };
            }

            this.open(
                {
                    'filters_modifier': filterModifier
                },
                'appendProducts',
                true
            );
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length && this.callbackName == 'appendProducts') {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                this._disableRows(data.items);
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
        },

        /**
         * @private
         */
        _handleManualGridSelect: function (selected) {
            if (this.callbackName == 'appendProducts') {
                var selectedRows = _.filter(this.productsProvider().data.items, function (row) {
                        return selected.indexOf(row['entity_id']) != -1;
                    }),
                    selectedVariationKeys = _.values(this._getVariationKeyMap(selectedRows));
                this._disableRows(this.productsProvider().data.items, selectedVariationKeys, selected);
            }
        },

        /**
         * Get variation key map used in manual grid.
         *
         * @param items
         * @returns {Array} [{entity_id: variation-key}, ...]
         * @private
         */
        _getVariationKeyMap: function (items) {
            this._variationKeyMap = {};

            _.each(items, function (row) {
                this._variationKeyMap[row['entity_id']] = _.values(
                    _.pick(row, this._getAttributesCodes())
                ).sort().join('-');

            }, this);

            return this._variationKeyMap;
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
