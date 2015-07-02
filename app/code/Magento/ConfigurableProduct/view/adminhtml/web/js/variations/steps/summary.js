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
        nextLabel: $.mage.__('Generate Products'),

        /**
         * @param attributes example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (attributes) {
            return _.reduce(attributes, function(matrix, attribute) {
                var tmp = [];
                _.each(matrix, function(variations){
                    _.each(attribute.chosen, function(option){
                        tmp.push(_.union(variations, [option]));
                    });
                });
                if (!tmp.length) {
                    return _.map(attribute.chosen, function(option){
                        return [option];
                    });
                }
                return tmp;
            }, []);
        },
        generateGrid: function (variations, getSectionValue) {
            //['a1','b1','c1','d1'] option = {label:'a1', value:'', section:{img:'',inv:'',pri:''}}
            return _.map(variations, function (options) {
                var variation = [];
                //images
                variation.push(getSectionValue('images', options));
                //sku
                variation.push(_.reduce(options, function (memo, option) {
                    return memo + '-' + option.label;
                }, '').substring(1));
                //inventory
                variation.push(getSectionValue('inventory', options));
                //attributes
                _.each(options, function (option) {
                    variation.push(option.label);
                });
                //pricing
                variation.push(getSectionValue('pricing', options));

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

            this.grid(this.generateGrid(this.generateVariation(this.attributes()), wizard.data.sectionHelper));
        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
