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
        sections: ko.observableArray([]),
        attributes: ko.observableArray([]),
        grid: ko.observableArray([]),
        attributesName: ko.observableArray([]),
        nextLabel: $.mage.__('Generate Products'),
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
            this.variations = [];
            return _.map(variations, function (options) {
                var variation = [], images, sku, quantity, price;
                images = getSectionValue('images', options);
                variation.push(images);
                sku = productName + _.reduce(options, function (memo, option) {
                    return memo + '-' + option.label;
                }, '');
                variation.push(sku);
                quantity = getSectionValue('quantity', options);
                variation.push(quantity);
                //attributes
                _.each(options, function (option) {
                    variation.push(option.label);
                });
                price = getSectionValue('price', options);
                price = price || productPrice;
                variation.push('$ ' + price);
                this.variations.push({options: options, images: images, sku: sku, quantity: quantity, price: price});
                return variation;
            }, this);
        },
        render: function (wizard) {
            this.sections(wizard.data.sections());
            this.attributes(wizard.data.attributes());

            this.attributesName([$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Quantity'), $.mage.__('Price')]);
            this.attributes.each(function (attribute, index) {
                this.attributesName.splice(3 + index, 0, attribute.label);
            }, this);

            this.grid(this.generateGrid(this.generateVariation(this.attributes()), wizard.data.sectionHelper));
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
