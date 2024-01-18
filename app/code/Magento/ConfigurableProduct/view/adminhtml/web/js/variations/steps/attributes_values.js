/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'mage/translate'
], function (Component, $, ko, _, utils, Collapsible) {
    'use strict';

    //connect items with observableArrays
    ko.bindingHandlers.sortableList = {
        /** @inheritdoc */
        init: function (element, valueAccessor) {
            var list = valueAccessor();

            $(element).sortable({
                axis: 'y',
                handle: '[data-role="draggable"]',
                tolerance: 'pointer',

                /** @inheritdoc */
                update: function (event, ui) {
                    var item = ko.contextFor(ui.item[0]).$data,
                        position = ko.utils.arrayIndexOf(ui.item.parent().children(), ui.item[0]);

                    if (ko.contextFor(ui.item[0]).$index() != position) { //eslint-disable-line eqeqeq
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

    return Collapsible.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            createOptionsUrl: null,
            attributes: [],
            stepInitialized: false
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.createAttribute = _.wrap(this.createAttribute, function () {
                var args = _.toArray(arguments),
                    createAttribute = args.shift();

                return this.doInitSavedOptions(createAttribute.apply(this, args));
            });
            this.createAttribute = _.memoize(this.createAttribute.bind(this), _.property('id'));
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['attributes']);

            return this;
        },

        /**
         * Create option.
         */
        createOption: function () {
            // this - current attribute
            this.options.push({
                value: 0,
                label: '',
                id: utils.uniqueid(),
                'attribute_id': this.id,
                'is_new': true
            });
        },

        /**
         * @param {Object} option
         */
        saveOption: function (option) {
            if (this.isValidOption(option)) {
                this.options.remove(option);
                this.options.push(option);
                this.chosenOptions.push(option.id);
            }
        },

        /**
         * @param {Object} newOption
         * @return boolean
         */
        isValidOption: function (newOption) {
            var duplicatedOptions = [],
                errorOption,
                allOptions = [];

            newOption.label = newOption.label.trim();

            if (_.isEmpty(newOption.label)) {
                return false;
            }

            _.each(this.options(), function (option) {
                if (!_.isUndefined(allOptions[option.label]) && newOption.label === option.label) {
                    duplicatedOptions.push(option);
                }

                allOptions[option.label] = option.label;
            });

            if (duplicatedOptions.length) {
                _.each(duplicatedOptions, function (duplicatedOption) {
                    errorOption = $('[data-role="' + duplicatedOption.id + '"]');
                    errorOption.addClass('_error');
                });

                return false;
            }

            return true;
        },

        /**
         * @param {Object} option
         */
        removeOption: function (option) {
            this.options.remove(option);
        },

        /**
         * @param {String} attribute
         */
        removeAttribute: function (attribute) {
            this.attributes.remove(attribute);
            this.wizard.setNotificationMessage(
                $.mage.__('An attribute has been removed. This attribute will no longer appear in your configurations.')
            );
        },

        /**
         * @param {Object} attribute
         * @param {*} index
         * @return {Object}
         */
        createAttribute: function (attribute, index) {
            attribute.chosenOptions = ko.observableArray([]);
            attribute.options = ko.observableArray(_.map(attribute.options, function (option) {
                option.id = utils.uniqueid();

                return option;
            }));
            attribute.opened = ko.observable(this.initialOpened(index));
            attribute.collapsible = ko.observable(true);
            attribute.isValidOption = this.isValidOption;

            return attribute;
        },

        /**
         * First 3 attribute panels must be open.
         *
         * @param {Number} index
         * @return {Boolean}
         */
        initialOpened: function (index) {
            return index < 3;
        },

        /**
         * Save attribute.
         */
        saveAttribute: function () {
            var errorMessage = $.mage.__('Select options for all attributes or remove unused attributes.');

            if (!this.attributes().length) {
                throw new Error(errorMessage);
            }

            _.each(this.attributes(), function (attribute) {
                attribute.chosen = [];

                if (!attribute.chosenOptions.getLength()) {
                    throw new Error(errorMessage);
                }
                _.each(attribute.chosenOptions(), function (id) {
                    attribute.chosen.push(attribute.options.findWhere({
                        id: id
                    }));
                });
            });
        },

        /**
         * @param {Object} attribute
         */
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'id'));
        },

        /**
         * @param {Object} attribute
         */
        deSelectAllAttributes: function (attribute) {
            attribute.chosenOptions.removeAll();
        },

        /**
         * @return {Boolean}
         */
        saveOptions: function () {
            var newOptions = [];

            _.each(this.attributes(), function (attribute) {
                _.each(attribute.options(), function (element) {
                    var option = attribute.options.findWhere({
                        id: element.id
                    });

                    if (option['is_new'] === true) {
                        if (!attribute.isValidOption(option)) {
                            throw new Error(
                                $.mage.__('The value of attribute ""%1"" must be unique')
                                    .replace('"%1"', attribute.label)
                            );
                        }

                        newOptions.push(option);
                    }
                });
            });

            if (!newOptions.length) {
                return false;
            }

            $.ajax({
                type: 'POST',
                url: this.createOptionsUrl,
                data: {
                    options: newOptions
                },
                showLoader: true
            }).done(function (savedOptions) {
                if (savedOptions.error) {
                    this.notificationMessage.error = savedOptions.error;
                    this.notificationMessage.text = savedOptions.message;

                    return;
                }

                _.each(this.attributes(), function (attribute) {
                    _.each(savedOptions, function (newOptionId, oldOptionId) {
                        var option = attribute.options.findWhere({
                            id: oldOptionId
                        });

                        if (option) {
                            attribute.options.remove(option);
                            option['is_new'] = false;
                            option.value = newOptionId;
                            attribute.options.push(option);
                        }
                    });
                });

            }.bind(this));
        },

        /**
         * @param {*} attributeIds
         */
        requestAttributes: function (attributeIds) {
            $.ajax({
                type: 'GET',
                url: this.optionsUrl,
                data: {
                    attributes: attributeIds
                },
                showLoader: true
            }).done(function (attributes) {
                attributes = _.sortBy(attributes, function (attribute) {
                    return this.wizard.data.attributesIds.indexOf(attribute.id);
                }.bind(this));
                this.attributes(_.map(attributes, this.createAttribute));
            }.bind(this));
        },

        /**
         * @param {*} attribute
         * @return {*}
         */
        doInitSavedOptions: function (attribute) {
            var selectedOptions, selectedOptionsIds, selectedAttribute = _.findWhere(this.initData.attributes, {
                id: attribute.id
            });

            if (selectedAttribute) {
                selectedOptions = _.pluck(selectedAttribute.chosen, 'value');
                selectedOptionsIds = _.pluck(_.filter(attribute.options(), function (option) {
                    return _.contains(selectedOptions, option.value);
                }), 'id');
                attribute.chosenOptions(selectedOptionsIds);
                this.initData.attributes = _.without(this.initData.attributes, selectedAttribute);
            }

            return attribute;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.requestAttributes(wizard.data.attributesIds());
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            this.saveOptions();
            this.saveAttribute(wizard);

            wizard.data.attributes = this.attributes;
        },

        /**
         * @param {Object} wizard
         */
        back: function (wizard) {
            wizard.data.attributesIds(this.attributes().pluck('id'));
        }
    });
});
