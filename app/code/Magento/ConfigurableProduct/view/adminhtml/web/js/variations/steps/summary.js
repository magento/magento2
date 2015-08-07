/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'uiRegistry'
], function (Component, $, ko, _, uiRegistry) {
    'use strict';

    var viewModel;
    viewModel = Component.extend({
        defaults: {
            gridExisting: [],
            gridNew: [],
            gridDeleted: [],
            attributes: [],
            attributesName: [],
            sections: [],
            productAttributesMap: null,
            gridTemplate: 'Magento_ConfigurableProduct/variations/steps/summary-grid'
        },
        initObservable: function () {
            this._super().observe('gridExisting gridNew gridDeleted attributes attributesName sections');
            return this;
        },
        nextLabelText: $.mage.__('Generate Products'),
        variationsComponent: uiRegistry.async('configurableVariations'),
        variations: [],
        /**
         * @param attributes example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (attributes) {
            return _.reduce(attributes, function(matrix, attribute) {
                var tmp = [];
                _.each(matrix, function(variations){
                    _.each(attribute.chosen, function(option){
                        option.attribute_code = attribute.code;
                        option.attribute_label = attribute.label;
                        tmp.push(_.union(variations, [option]));
                    });
                });
                if (!tmp.length) {
                    return _.map(attribute.chosen, function(option){
                        option.attribute_code = attribute.code;
                        option.attribute_label = attribute.label;
                        return [option];
                    });
                }
                return tmp;
            }, []);
        },
        generateGrid: function (variations, getSectionValue) {
            //['a1','b1','c1','d1'] option = {label:'a1', value:'', section:{img:'',inv:'',pri:''}}
            var productName = this.variationsComponent().getProductValue('name');
            var productPrice = this.variationsComponent().getProductValue('price');
            var variationsKeys = [];
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
                var product = this.getProductByOptions(options);
                var variation = {
                    options: options,
                    images: images,
                    sku: sku,
                    quantity: quantity,
                    price: price,
                    product_id: product,
                    editable: true
                };
                this.variations.push(variation);
                if (product) {
                    this.gridExisting.push(this.prepareRowForGrid(variation));
                } else {
                    this.gridNew.push(this.prepareRowForGrid(variation));
                }
                variationsKeys.push(this.getVariationKey(options));
            }, this);

            _.each(_.omit(this.productAttributesMap, variationsKeys), function (productId, productKey) {
                this.gridDeleted.push(this.prepareRowForGrid(
                    _.findWhere(this.initData.configurations, {product_id: productId})
                ));
            }.bind(this));
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
        getVariationKey: function (options) {
            return _.pluck(options, 'value').sort().join('-');
        },
        getProductByOptions: function (options) {
            return this.productAttributesMap[this.getVariationKey(options)] || null;
        },
        initProductAttributesMap: function () {
            if (null === this.productAttributesMap) {
                this.productAttributesMap = {};
                _.each(this.initData.configurations, function(product) {
                    this.productAttributesMap[this.getVariationKey(product.options)] = product.product_id;
                }.bind(this));
            }
        },
        render: function (wizard) {
            this.initProductAttributesMap();
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
            this.generateGrid(this.generateVariation(this.attributes()), wizard.data.sectionHelper);
        },
        force: function (wizard) {
            this.variationsComponent().render(this.variations, this.attributes());
            $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
