/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiCollection',
    'jquery',
    'ko',
    'uiLayout',
    'mageUtils',
    'underscore'
], function (Collection, $, ko, layout, utils, _) {
    'use strict';

    return Collection.extend({
        defaults: {
            attribute: {},
            template: 'Magento_InventoryConfigurableProduct/container',
            identifier: 'source_code',
            dataScope: '',
            currentAttribute: '',
            insertListingComponent: '',
            dynamicRowsName: 'dynamicRows',
            type: '',
            templateElementNames: {
                button: 'button_template',
                dynamicRows: 'dynamic_rows_template',
                group: 'group_template'
            },
            ignoreTmpls: {
                childTemplate: true
            },
            listens: {
                'attribute': 'handlerAttributeChange',
                'insertListingValue': 'handlerInsertValueChange',
                'type': 'handlerTypeChange'
            },
            dynamicRowsCollection: {},
            imports: {
                insertListingValue: '${$.insertListingComponent}:value'
            },
            modules: {
                insertListing: '${$.insertListingComponent}'
            }
        },

        /**
         * @param {Array} firstArray
         * @param {Array} secondArray
         * @param {String} identifier
         *
         * @returns {Array}
         */
        getChanges: function (firstArray, secondArray, identifier) {
            return firstArray.filter(function(item){
                return !secondArray.filter(function(data){
                    return data[identifier] === item[identifier]
                }).length;
            }.bind(this));
        },

        /**
         * Generates data for dynamic-rows records
         * @param {Array} data
         *
         * @returns {Array}
         */
        generateDynamicRowsData: function (data) {
            var items = [];

            _.each(data, function (item) {
                items.push({
                    'source': item.name,
                    'source_code': item[this.identifier]
                })
            }.bind(this));

            return items;
        },

        /**
         * Handler for InsertListing value
         *
         * @param {Array} data
         */
        handlerInsertValueChange: function (data) {
            var items;

            if (!this.currentDynamicRows) {
                return;
            }

            data = this.getChanges(data, this[this.currentDynamicRows](), this.identifier);

            if (!data.length) {
                return;
            }

            items = this.generateDynamicRowsData(data);
            this[this.currentDynamicRows](items);
        },

        /**
         * Handler for attribute property
         *
         * @param {Object} data
         */
        handlerAttributeChange: function (data) {
            if (data && data !== this.currentAttribute) {
                this.currentAttribute = data;
                this.destroyChildren();

                _.each(data.chosen, function (item) {
                    this.addChild(item)
                }.bind(this));
            }
        },

        /**
         * Handler for type property
         *
         * @param {String} data
         */
        handlerTypeChange: function (data) {
            if (data === 'single') {
                this.destroyChildren();
                this.currentAttribute = {};
                this.addChild();
            } else if (data === 'each' && this.attribute) {
                this.handlerAttributeChange(this.attribute);
            }
        },

        /** @inheritdoc */
        validate: function (elem) {
            if (typeof elem === 'undefined') {
                return;
            }

            if (typeof elem.validate === 'function') {
                this.valid = this.valid & elem.validate().valid;
            } else if (elem.elems) {
                elem.elems().forEach(this.validate, this);
            }
        },

        /**
         * Parses string templates.
         * Skip parse if deferredTmpl property set to "true"
         *
         * @param {Object} obj
         *
         * @returns obj
         */
        parseTemplateString: function (obj) {
            var children;

            if (obj.children) {
                children = utils.copy(obj.children);
                delete obj.children;
            }

            obj = utils.template(obj, obj);
            obj.children = children;

            if (children) {
                _.each(children, function (child, name) {
                    obj.children[name] = child.config.deferredTmpl ? child : this.parseTemplateString(child);
                }, this);
            }

            obj.name = obj.config.name || obj.name;

            return obj;
        },

        /**
         * Handler for modal
         *
         * @param {String} id - dynamic-rows name that open modal
         */
        handleToggleSourcesModal: function (id) {
            this.currentDynamicRows = id;
            this.insertListing().value(this[id]())
        },

        /**
         * @param {String} key - prop in current context.
         */
        makeObservable: function (key) {
            if (typeof this[key] === 'function') {
                delete this[key];
            }

            this.observe(key);
        },

        /**
         * Generates dynamic data for child.
         *
         * @param {Object | Undefined} data - optional.
         */
        generateDynamicData: function (data) {
            var key = data ? data.label : this.dynamicRowsName;

            return {
                group: {
                    name: key
                },
                button: {
                    label: data ? data.label : 'Quantity',
                    targetName: this.name,
                    param: key
                },
                dynamicRows: {
                    dataScope: 'data.' + this.name + '.' + key,
                    dataProvider: this.name + ':' + key,
                    name: this.dynamicRowsName,
                    exportTo: this.name + ':dynamicRowsCollection.' +
                    (this.type === 'each' ? this.currentAttribute.code + '.' : '') + key
                }
            };
        },

        /**
         * @param {Object | Undefined} data - optional.
         */
        addChild: function (data) {
            var template = utils.copy(this.childTemplate['templates']).children,
                dynamicRows = template[this.templateElementNames.dynamicRows],
                button = template[this.templateElementNames.button],
                group = template[this.templateElementNames.group],
                key = data ? data.label : this.dynamicRowsName,
                dynamicData = this.generateDynamicData(data);

            this.makeObservable(key);

            group.dynamicData = dynamicData.group;
            group.parent = this.name;
            group = this.parseTemplateString(group);

            button.dynamicData = dynamicData.button;
            button.parent = this.name + '.' + group.name;
            button = this.parseTemplateString(button);

            dynamicRows.dynamicData = dynamicData.dynamicRows;
            dynamicRows.parent = this.name + '.' + group.name;
            dynamicRows = this.parseTemplateString(dynamicRows);

            layout([group, button, dynamicRows]);
        }
    });
});
