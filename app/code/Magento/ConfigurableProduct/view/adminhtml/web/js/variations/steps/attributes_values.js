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

    //connect items with observableArrays
    ko.bindingHandlers.sortableList = {
        init: function(element, valueAccessor) {
            var list = valueAccessor();
            $(element).sortable({
                axis: 'y',
                handle: '[data-role="draggable"]',
                tolerance: 'pointer',
                update: function(event, ui) {
                    var item = ko.contextFor(ui.item[0]).$data;
                    var position = ko.utils.arrayIndexOf(ui.item.parent().children(), ui.item[0]);
                    if (ko.contextFor(ui.item[0]).$index() != position) {
                        if (position >= 0) {
                            list.remove(item);
                            list.splice(position, 0, item);
                        }
                        ui.item.remove();
                    }
                }
            });
        }
    };

    var viewModel = Collapsible.extend({
        attributes: ko.observableArray([]),
        createOption: function (attribute) {
            attribute.options.push({value: 0, label: ''});
        },
        saveOption: function (option) {
            this.options.remove(option);
            this.options.push(option);
            this.chosenOptions.push(option.value);
        },
        removeOption: function (option) {
            this.options.remove(option);
        },
        removeAttribute: function (attribute) {
            viewModel.prototype.attributes.remove(attribute);
            this.wizard.notifyMessage(
                $.mage.__('An attribute has been removed. This attribute will no longer appear in your configurations.'),
                false
            );
        },
        createAttribute: function (attribute, index) {
            attribute.chosenOptions = ko.observableArray([]);
            attribute.options = ko.observableArray(attribute.options);
            attribute.opened = ko.observable(this.initialOpened(index));
            attribute.collapsible = ko.observable(true);
            return attribute;
        },
        //first 3 attribute panels must be open
        initialOpened: function (index) {
            return index < 3;
        },
        saveAttribute: function () {
            this.attributes.each(function(attribute) {
                attribute.chosen = [];
                attribute.chosenOptions.each(function(key) {
                    attribute.chosen.push(attribute.options.findWhere({value:key}));
                });
            });
        },
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'value'));
        },
        render: function(wizard) {
            this.wizard = wizard;
            $.ajax({
                type: "POST",
                url: this.options_url,
                data: {attributes: wizard.data.attributes()},
                showLoader: true
            }).done(function(attributes){
                this.attributes(_.map(attributes, this.createAttribute, this));
            }.bind(this));
        },
        force: function(wizard) {
            viewModel.prototype.saveAttribute(wizard);

            wizard.data.attributes = this.attributes;
        },
        back: function(wizard) {
            wizard.data.attributes(viewModel.prototype.attributes().pluck('id'));
        }
    });
    return viewModel;
});
