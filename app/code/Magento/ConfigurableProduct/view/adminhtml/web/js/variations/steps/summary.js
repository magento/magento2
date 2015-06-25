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

    var viewModel;
    viewModel = Component.extend({
        sections: ko.observableArray([]),
        attributes: ko.observableArray([]),
        grid: ko.observableArray([]),
        attributesName: ko.observableArray([]),

        /**
         * @param attributes example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (attributes) {
            return _.reduce(attributes, function(matrix, attribute) {
                var tmp = [];
                _.each(matrix, function(variations){
                    _.each(attribute.chosen, function(option){
                        if (_.isArray(variations)){
                            tmp.push(_.union(variations, [option]));
                        } else {
                            tmp.push([variations, option]);
                        }
                    });
                });
                if (tmp.length < 1) {
                    return _.map(attribute.chosen, function(option){
                        return [option];
                    });
                }
                return tmp;
            }, []);
        },
        generateGrid: function (variations) {
            //['a1','b1','c1','d1'] option = {label:'a1', value:'', section:{img:'',inv:'',pri:''}}
            return _.map(variations, function (options) {
                //sku
                var variation;
                var pricing;
                var inventory;
                var images;
                var sku;
                var other;
                var needNames;
                sku = _.reduce(options, function (memo, option) {
                    return memo + option.label;
                }, '');
                //images
                images = _.find(options, function (option) {
                    return !_.isEmpty(option.sections().images);
                });
                images = images
                    ? images.sections().images
                    : _.findWhere(this.sections(), {label:'images'}).value();
                //inventory
                inventory = _.find(options, function (option) {
                    return !_.isEmpty(option.sections().inventory);
                });
                inventory = inventory
                    ? inventory.sections().inventory
                    : _.findWhere(this.sections(), {label:'inventory'}).value();
                //pricing
                pricing = _.find(options, function (option) {
                    return !_.isEmpty(option.sections().pricing);
                });
                pricing = pricing
                    ? pricing.sections().pricing
                    : _.findWhere(this.sections(), {label:'pricing'}).value();

                variation = [images, sku, inventory, pricing];

                _.each(options, function (option, index) {
                    variation.splice(3 + index, 0, option.label);
                }, this);

                //result
                return variation;
            }, this);
        },
        render: function (wizard) {
            this.sections(wizard.data.sections());
            this.attributes(wizard.data.attributes());

            this.attributesName(['images','sku','inventory','price']);
            this.attributes.each(function (attribute, index) {
                this.attributesName.splice(3 + index, 0, attribute.label);
            }, this);

            this.grid(this.generateGrid(this.generateVariation(this.attributes())));
        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
