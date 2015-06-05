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

    var viewModel = Collapsible.extend({
        attributes: ko.observableArray([]),
        createOption: function (attribute) {
            attribute.options.push({id:0, label:''});
        },
        saveOption: function (option) {
            this.options.remove(option);
            //TODO: ajax request to save attribute
            var id = _.uniqueId()+this.id;
            this.options.push({id:id, label:option.label});
            this.chosenOptions.push(id);
        },
        removeAttribute: function (attribute) {
            viewModel.prototype.attributes.remove(attribute);
        },
        removeOption: function (option) {
            this.options.remove(option);
        },
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'id'));
        },
        showCreateOption: function (element) {
            $(element).append($('[data-action="addOption"]'));
        },
        render: function(wizard) {
            $.ajax({
                type: "POST",
                url: this.options_url,
                data: {attributes: wizard.data},
                showLoader: true
            }).done(function(data){
                this.attributes(_.map(data.attributes, function (attribute) {
                    attribute.options = ko.observableArray(attribute.options);
                    attribute.chosenOptions = ko.observableArray(_.pluck(attribute.options, 'id'));
                    return attribute;
                }, this));
            });
        },
        force: function(wizard) {
            return wizard.data.attributes;
        },
        back: function(wizard) {
        }
    });
    return viewModel;
});
