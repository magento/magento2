/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (Component, $, ko, _, Collapsible) {
    'use strict';

    var viewModel = Component.extend({
        attributes: ko.observableArray([]),
        createOption: function (attribute) {
            attribute.options.push({value:0, label:''});
        },
        saveOption: function (option) {
            this.options.remove(option);
            //TODO: ajax request to save attribute
            var value = _.uniqueId()+this.id;
            this.options.push({value:value, label:option.label});
            this.chosenOptions.push(value);
        },
        removeOption: function (option) {
            this.options.remove(option);
        },
        removeAttribute: function (attribute) {
            viewModel.prototype.attributes.remove(attribute);
        },
        createAttribute: function (attribute) {
            attribute.chosenOptions = ko.observableArray([]);
            attribute.options = ko.observableArray(attribute.options);
            return attribute;
        },
        saveAttribute: function (attribute) {
            attribute.chosenOptions = ko.observableArray([]);
            attribute.options = ko.observableArray(attribute.options);
            return attribute;
        },
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'value'));
        },
        render: function(wizard) {
            $.ajax({
                type: "POST",
                url: this.options_url,
                data: {attributes: wizard.data.attributes},
                showLoader: true
            }).done(function(attributes){
                viewModel.prototype.attributes(_.map(attributes, this.createAttribute, this));
            });
        },
        force: function(wizard) {
            this.attributes.map(function(attribute) {
                attribute.chosen = [];
                attribute.chosenOptions.each(function(key) {
                    attribute.chosen.push(_.where(attribute.options(), {value:key}));
                });
            });

            wizard.data.attributesValues = ko.toJS(this.attributes);
        },
        back: function(wizard) {
        }
    });
    return viewModel;
});
