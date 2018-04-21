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
    'underscore',
    'mage/translate'
], function (Collection, $, ko, layout, utils, _, $t) {
    'use strict';

    return Collection.extend({
        defaults: {
            attribute: {},
            template: 'Magento_InventoryConfigurableProductAdminUi/container',
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
                    'source_code': item[this.identifier],
                    'source_status': parseInt(item.enabled, 10) ? $.mage.__('Enabled') : $.mage.__('Disabled')
                });
            }.bind(this));

            return items;
        },

        /**
         * Handler for InsertListing value
         *
         * @param {Array} data
         */
        handlerInsertValueChange: function (data) {
            var items,
                path = this.dynamicRowsName + '.' + this.currentDynamicRows;

            if (!this.currentDynamicRows) {
                return;
            }

            if (!data.length) {
                return;
            }

            items = this.generateDynamicRowsData(data);
            this.source.set(path, items);
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
                    this.addChild(item);
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
            this.currentDynamicRows = this.type === 'each' ? this.currentAttribute.code + '.' + id : id;
            this.insertListing().value(this.source.get(this.dynamicRowsName + '.' + this.currentDynamicRows));
        },

        /**
         * Generates dynamic data for child.
         *
         * @param {Object | Undefined} data - optional.
         */
        generateDynamicData: function (data) {
            var key = data ? data.label : this.dynamicRowsName,
                drExportTo = this.name + ':dynamicRowsCollection.',
                drDataScope = 'data.' + this.name + '.',
                drDataProvider = this.dynamicRowsName + '.';

            if (this.type === 'each') {
                drDataScope += this.currentAttribute.code + '.';
                drExportTo += this.currentAttribute.code + '.';
                drDataProvider += this.currentAttribute.code + '.';
            }

            drDataScope += key;
            drExportTo += key;
            drDataProvider += key;

            return {
                group: {
                    name: key
                },
                button: {
                    label: data ? data.label : $t('Quantity'),
                    targetName: this.name,
                    param: key
                },
                dynamicRows: {
                    dataScope: drDataScope,
                    dataProvider: drDataProvider,
                    name: this.dynamicRowsName,
                    exportTo: drExportTo
                }
            };
        },

        /**
         * @param {Object | Undefined} data - optional.
         */
        addChild: function (data) {
            var template = utils.copy(this.childTemplate.templates).children,
                dynamicRows = template[this.templateElementNames.dynamicRows],
                button = template[this.templateElementNames.button],
                group = template[this.templateElementNames.group],
                dynamicData = this.generateDynamicData(data);

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
