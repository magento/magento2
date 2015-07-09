/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible'
], function (Component, $, ko, _, utils, Collapsible) {
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

    var saveAttributes = _.memoize(function (attributes) {
        return _.map(attributes, this.createAttribute, this);
    }, function(attributes) {
        return _.reduce(attributes, function (memo, attribute) {
            return memo + attribute.id;
        });
    });

    return Collapsible.extend({
        attributes: ko.observableArray([]),
        createOption: function () {
            // this - current attribute
            this.options.push({value: 0, label: '', id: utils.uniqueid(), attribute_id: this.id, is_new: true});
        },
        saveOption: function (option) {
            this.options.remove(option);
            this.options.push(option);
            this.chosenOptions.push(option.id);
        },
        removeOption: function (option) {
            this.options.remove(option);
        },
        removeAttribute: function (attribute) {
            this.attributes.remove(attribute);
            this.wizard.notifyMessage(
                $.mage.__('An attribute has been removed. This attribute will no longer appear in your configurations.'),
                false
            );
        },
        createAttribute: function (attribute, index) {
            attribute.chosenOptions = ko.observableArray([]);
            attribute.options = ko.observableArray(_.map(attribute.options, function (option) {
                option.id = utils.uniqueid();
                return option;
            }));
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
                if (!attribute.chosenOptions.getLength()) {
                    throw new Error($.mage.__('Select options for all attributes or remove unused attributes.'));
                }
                attribute.chosenOptions.each(function(id) {
                    attribute.chosen.push(attribute.options.findWhere({id:id}));
                });
            });
        },
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'id'));
        },
        deSelectAllAttributes: function (attribute) {
            attribute.chosenOptions.removeAll();
        },
        saveOptions: function() {
            var options = [];
            this.attributes.each(function(attribute) {
                attribute.chosenOptions.each(function(id) {
                    var option = attribute.options.findWhere({id:id, is_new: true});
                    if (option) {
                        options.push(option);
                    }
                });
            });
            if (!options.length) {
                return false;
            }
            $.ajax({
                type: "POST",
                url: this.createOptionsUrl,
                data: {options: options},
                showLoader: true
            }).done(function(options) {
                this.attributes.each(function(attribute) {
                    _.each(options, function(newOptionId, oldOptionId) {
                        var option = attribute.options.findWhere({id:oldOptionId});
                        if (option) {
                            attribute.options.remove(option);
                            option.is_new = false;
                            option.value = newOptionId;
                            attribute.options.push(option);
                        }
                    });
                });

            }.bind(this));
        },
        requestAttributes: function (attributeIds) {
            $.ajax({
                type: "POST",
                url: this.optionsUrl,
                data: {attributes: attributeIds},
                showLoader: true
            }).done(function(attributes){
                this.attributes(saveAttributes.call(this, attributes));
            }.bind(this));
        },
        render: function(wizard) {
            this.wizard = wizard;
            this.requestAttributes(wizard.data.attributesIds());
        },
        force: function(wizard) {
            this.saveOptions();
            this.saveAttribute(wizard);

            wizard.data.attributes = this.attributes;
        },
        back: function(wizard) {
            wizard.data.attributesIds(this.attributes().pluck('id'));
        }
    });
});
