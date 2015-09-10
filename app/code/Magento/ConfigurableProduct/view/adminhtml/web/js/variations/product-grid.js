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
            associatedProductsModal: null,
            associatedProductsGridUrl: '${ $.associatedProductsGridUrl }',
            attributes: [],
            gridSelector: '[data-grid-id=associated-products-container]',
            modules: {
                associatedProductsFilter: '${ $.associatedProductsFilter }',
                associatedProductsProvider: '${ $.associatedProductsProvider }',
                associatedProductsMassAction: '${ $.associatedProductsMassAction }',
                variationsComponent: '${ $.configurableVariations }'
            }
        },

        /**
         * @todo description
         */
        initialize: function (options) {
            this._super(options);

            var gridIsLoaded = false;
            this.associatedProductsModal = $(this.gridSelector).modal({
                title: $.mage.__('Select Associated Product'),
                type: 'slide',
                opened: function () {
                    if (!gridIsLoaded) {
                        $.ajax({
                            type: 'GET',
                            url: this._buildGridUrl(),
                            context: $('body')
                        }).success(function (data) {
                            gridIsLoaded = true;
                            bootstrap(JSON.parse(data));
                        }.bind(this));
                    }
                }.bind(this)
            });
        },

        /**
         * @todo description
         */
        initObservable: function () {
            this._super().observe('actions opened productMatrix');

            return this;
        },

        /**
         * Select different product in configurations section
         * @param rowIndex
         */
        selectProduct: function (rowIndex) {
            this.variationsComponent().chooseDifferentProduct(this.getProduct(rowIndex));
            this.close();
        },

        /**
         * @todo description
         */
        open: function (attributes, showMassActionColumn) {
            this.attributes = attributes;
            this.associatedProductsMassAction(function(massActionComponent) {
                massActionComponent.visible(showMassActionColumn);
            });
            this._setFilter();
            this._showMessageAssociatedGrid();
            this.associatedProductsModal.modal('openModal');
        },

        /**
         * @todo description
         */
        close: function () {
            this.associatedProductsModal.trigger('closeModal');
        },

        /**
         * @todo description
         */
        getProduct: function (rowIndex) {
            return this.associatedProductsProvider().data.items[rowIndex];
        },

        /**
         * Build grid url
         *
         * @returns {string}
         * @private
         */
        _buildGridUrl: function() {
            var params = this.attributes
                ? '?' + $.param({filters: this.attributes, attribute_ids: _.keys(this.attributes)})
                : '';
            return this.associatedProductsGridUrl + params;
        },

        /**
         * @todo description
         */
        _showMessageAssociatedGrid: function () {
            var messageInited = false;
            this.associatedProductsProvider(function(provider) {
                if (!messageInited) {
                    this.associatedProductsModal.notification();
                }
                this.associatedProductsModal.notification('clear');
                if (provider.data.items.length) {
                    this.associatedProductsModal.notification('add', {
                        message: $.mage.__('Choose a new product to delete and replace the current product configuration.'),
                        messageContainer: this.gridSelector
                    });
                } else {
                    this.associatedProductsModal.notification('add', {
                        message: $.mage.__('For better results, add attributes and attribute values to your products.'),
                        messageContainer: this.gridSelector
                    });
                }
            }.bind(this));
        },

        /**
         * @todo description
         */
        _setFilter: function () {
            this.associatedProductsProvider(function(provider) {
                provider.params['attribute_ids'] = _.keys(this.attributes);
            }.bind(this));
            this.associatedProductsFilter(function(filter) {
                filter.set('filters', this.attributes)
                .apply();
            }.bind(this));
        }
    });
});
