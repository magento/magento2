/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mage/translate'
], function (Component, $, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                variationsComponent: '${ $.variationsComponent }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            gridExisting: [],
            gridNew: [],
            gridDeleted: [],
            attributes: [],
            attributesName: [],
            sections: [],
            gridTemplate: 'Magento_ConfigurableProduct/variations/steps/summary-grid'
        },
        initObservable: function () {
            this._super().observe('gridExisting gridNew gridDeleted attributes attributesName sections');

            return this;
        },
        nextLabelText: $.mage.__('Generate Products'),
        variations: [],
        generateGrid: function (variations, getSectionValue) {
            var productName = this.variationsComponent().getProductValue('name');
            var productPrice = this.variationsComponent().getProductValue('price');
            var variationsKeys = [];
            var gridExisting = [];
            var gridNew = [];
            var gridDeleted = [];
            this.variations = [];

            _.each(variations, function (options) {
                var images, sku, quantity, price;
                images = getSectionValue('images', options);
                sku = productName + _.reduce(options, function (memo, option) {
                    return memo + '-' + option.label;
                }, '');
                quantity = getSectionValue('quantity', options);
                price = getSectionValue('price', options);
                price = price || productPrice;
                var productId = this.variationsComponent().getProductIdByOptions(options);
                if (productId && !images.file) {
                    var product = _.findWhere(this.variationsComponent().variations, {product_id: productId});
                    images = product.images;
                }
                var variation = {
                    options: options,
                    images: images,
                    sku: sku,
                    quantity: quantity,
                    price: price,
                    product_id: productId,
                    editable: true
                };
                this.variations.push(variation);
                if (productId) {
                    gridExisting.push(this.prepareRowForGrid(variation));
                } else {
                    gridNew.push(this.prepareRowForGrid(variation));
                }
                variationsKeys.push(this.variationsComponent().getVariationKey(options));
            }, this);

            this.gridExisting(gridExisting);

            if (gridNew.length > 0) {
                this.gridNew(gridNew);
            }

            _.each(_.omit(this.variationsComponent().productAttributesMap, variationsKeys), function (productId) {
                gridDeleted.push(this.prepareRowForGrid(
                    _.findWhere(this.variationsComponent().variations, {product_id: productId})
                ));
            }.bind(this));

            if (gridDeleted.length > 0) {
                this.gridDeleted(gridDeleted);
            }
        },
        prepareRowForGrid: function(variation) {
            var row = [];
            row.push(_.extend({images: []}, variation.images));
            row.push(variation.sku);
            row.push(variation.quantity);
            _.each(variation.options, function (option) {
                row.push(option.label);
            });
            row.push('$ ' + variation.price);

            return row;
        },
        getGridTemplate: function() {
            return this.gridTemplate;
        },
        getGridId: function() {
            return _.uniqueId('grid_');
        },
        render: function (wizard) {
            this.wizard = wizard;
            this.sections(wizard.data.sections());
            this.attributes(wizard.data.attributes());

            this.attributesName([$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Quantity'), $.mage.__('Price')]);
            this.attributes.each(function (attribute, index) {
                this.attributesName.splice(3 + index, 0, attribute.label);
            }, this);

            this.gridNew([]);
            this.gridExisting([]);
            this.gridDeleted([]);
            this.generateGrid(wizard.data.variations, wizard.data.sectionHelper);
        },
        force: function (wizard) {
            this.variationsComponent().render(this.variations, this.attributes());
            $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },
        back: function (wizard) {
        }
    });
});
