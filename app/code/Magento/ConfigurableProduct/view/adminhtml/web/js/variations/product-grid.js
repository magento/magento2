/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore'
], function (Component, $, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            associatedProductsModal: null,
            modules: {
                associatedProductsFilter: '${ $.associatedProductsFilter }',
                associatedProductsProvider: '${ $.associatedProductsProvider }',
                variationsComponent: '${ $.configurableVariations }'
            }
        },

        /**
         * @todo description
         */
        initialize: function () {
            this._super();

            this.associatedProductsModal = $('#associated-products-container').modal({
                title: $.mage.__('Select Associated Product'),
                type: 'slide'
            });
        },

        /**
         * @todo description
         */
        initObservable: function () {
            this._super().observe('actions opened attributes productMatrix');

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
        open: function (attributes) {
            if (attributes) {
                this._setFilter(attributes);
            }

            this._showMessageAssociatedGrid();
            this.associatedProductsModal.trigger('openModal');
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
         * @todo description
         */
        _showMessageAssociatedGrid: function () {
            if (this.associatedProductsProvider().data.items.length) {
                this.associatedProductsModal
                    .find('[data-role="messages"] div div')
                    .text($.mage.__('Choose a new product to delete and replace the current product configuration.'));
            } else {
                this.associatedProductsModal
                    .find('[data-role="messages"] div div')
                    .text($.mage.__('For better results, add attributes and attribute values to your products.'));
            }
        },

        /**
         * @todo description
         */
        _setFilter: function (attributes) {
            this.associatedProductsProvider().params['attribute_ids'] = _.keys(attributes);
            this.associatedProductsFilter().set('filters', attributes).apply();
        }
    });
});
