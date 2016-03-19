/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
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
            attributesName: [$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Quantity'), $.mage.__('Price')],
            sections: [],
            gridTemplate: 'Magento_ConfigurableProduct/variations/steps/summary-grid'
        },
        initObservable: function () {
            this._super().observe('gridExisting gridNew gridDeleted attributes sections');
            this.gridExisting.columns = ko.observableArray();
            this.gridNew.columns = ko.observableArray();
            this.gridDeleted.columns = ko.observableArray();

            return this;
        },
        nextLabelText: $.mage.__('Generate Products'),
        variations: [],
        generateGrid: function (variations, getSectionValue) {
            var productSku = this.variationsComponent().getProductValue('sku'),
                productPrice = this.variationsComponent().getProductValue('price'),
                productWeight = this.variationsComponent().getProductValue('weight'),
                variationsKeys = [],
                gridExisting = [],
                gridNew = [],
                gridDeleted = [];
            this.variations = [];

            _.each(variations, function (options) {
                var product, images, sku, quantity, price, variation,
                    productId = this.variationsComponent().getProductIdByOptions(options);

                if (productId) {
                    product = _.findWhere(this.variationsComponent().variations, {
                        productId: productId
                    });
                }
                images = getSectionValue('images', options);
                sku = productSku + _.reduce(options, function (memo, option) {
                    return memo + '-' + option.label;
                }, '');
                quantity = getSectionValue('quantity', options);

                if (!quantity && productId) {
                    quantity = product.quantity;
                }
                price = getSectionValue('price', options);

                if (!price) {
                    price = productId ? product.price : productPrice;
                }

                if (productId && !images.file) {
                    images = product.images;
                }
                variation = {
                    options: options,
                    images: images,
                    sku: sku,
                    quantity: quantity,
                    price: price,
                    productId: productId,
                    weight: productWeight,
                    editable: true
                };

                if (productId) {
                    variation.sku = product.sku;
                    variation.weight = product.weight;
                    gridExisting.push(this.prepareRowForGrid(variation));
                } else {
                    gridNew.push(this.prepareRowForGrid(variation));
                }
                this.variations.push(variation);
                variationsKeys.push(this.variationsComponent().getVariationKey(options));
            }, this);

            this.gridExisting(gridExisting);
            this.gridExisting.columns(this.getColumnsName(this.wizard.data.attributes));

            if (gridNew.length > 0) {
                this.gridNew(gridNew);
                this.gridNew.columns(this.getColumnsName(this.wizard.data.attributes));
            }

            _.each(_.omit(this.variationsComponent().productAttributesMap, variationsKeys), function (productId) {
                gridDeleted.push(this.prepareRowForGrid(
                    _.findWhere(this.variationsComponent().variations, {
                        productId: productId
                    })
                ));
            }.bind(this));

            if (gridDeleted.length > 0) {
                this.gridDeleted(gridDeleted);
                this.gridDeleted.columns(this.getColumnsName(this.variationsComponent().productAttributes));
            }
        },
        prepareRowForGrid: function (variation) {
            var row = [];
            row.push(_.extend({
                images: []
            }, variation.images));
            row.push(variation.sku);
            row.push(variation.quantity);
            _.each(variation.options, function (option) {
                row.push(option.label);
            });
            row.push(this.variationsComponent().getCurrencySymbol() +  ' ' + variation.price);

            return row;
        },
        getGridTemplate: function () {
            return this.gridTemplate;
        },
        getGridId: function () {
            return _.uniqueId('grid_');
        },
        getColumnsName: function (attributes) {
            var columns = this.attributesName.slice(0);

            attributes.each(function (attribute, index) {
                columns.splice(3 + index, 0, attribute.label);
            }, this);

            return columns;
        },
        render: function (wizard) {
            this.wizard = wizard;
            this.sections(wizard.data.sections());
            this.attributes(wizard.data.attributes());
            this.gridNew([]);
            this.gridExisting([]);
            this.gridDeleted([]);
            this.generateGrid(wizard.data.variations, wizard.data.sectionHelper);
        },
        force: function () {
            this.variationsComponent().render(this.variations, this.attributes());
            $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },
        back: function () {
        }
    });
});
