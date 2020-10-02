/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'uiLayout',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/column'
], function (_, utils, $t, layout, registry, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_ImportExport/export/filter-grid/cells/filter',
            templates: {
                base: {
                    entity: '${ $.$data.column.entity }',
                    code: '${ $.$data.code }',
                    parent: '${ $.$data.column.name }',
                    name: '${ $.entity }_${ $.code }',
                    provider: '${ $.$data.column.name }',
                    dataScope: 'filters.${ $.code }'
                },
                text: {
                    component: 'Magento_Ui/js/grid/columns/column',
                    elementTmpl: 'ui/grid/cells/text'
                },
                input: {
                    component: 'Magento_Ui/js/form/element/abstract'
                },
                select: {
                    component: 'Magento_Ui/js/form/element/select',
                    options: '${ JSON.stringify($.$data.options) }',
                    caption: $t('-- Not Selected --')
                },
                multiselect: {
                    component: 'Magento_Ui/js/form/element/multiselect',
                    options: '${ JSON.stringify($.$data.options) }'
                },
                number: {
                    component: 'Magento_ImportExport/js/export/filter-grid/columns/filter/range',
                    rangeType: 'text'
                },
                date: {
                    component: 'Magento_ImportExport/js/export/filter-grid/columns/filter/range',
                    rangeType: 'date'
                }
            },
            filters: {},
            imports: {
                entity: '${ $.provider }:params.entity',
                onDataUpdate: '${ $.provider }:data',
                onEntityUpdate: 'entity'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Object} Chainable
         */
        initObservable: function () {
            this._super()
                .observe({
                    elems: []
                });

            return this;
        },

        /**
         * Retrieves ui component from the 'elems' array, related with the row.
         *
         * @param {Object} row
         * @returns {Object[]}
         */
        getChild: function (row) {
            var elem = _.findWhere(this.elems(), {
                entity: this.entity,
                code: row[this.indexField]
            });

            return elem ? [elem] : [];
        },

        /**
         * Requests specified components to insert them into 'elems' array.
         *
         * @param {(String|Array)} elems
         * @returns {Object} Chainable
         */
        insertChild: function (elems) {
            if (!_.isArray(elems)) {
                elems = [elems];
            }

            elems.forEach(function (elem) {
                registry.get(elem, function () {
                    this.elems.push(elem);
                }.bind(this));
            }, this);

            return this;
        },

        /**
         * Creates template for ui component, based on the row type.
         *
         * @param {Object} row
         * @returns {Object}
         */
        getRowTemplate: function (row) {
            var templates = this.templates,
                type      = row[this.index].type || 'text',
                template  = utils.extend({}, templates.base, templates[type]);

            return utils.template(template, {
                column: this,
                code: row[this.indexField],
                options: row[this.index].options || {}
            }, true, true);
        },

        /**
         * Executed whenever data in the provider is updated,
         * creates appropriate ui components.
         *
         * @param {Object} data
         */
        onDataUpdate: function (data) {
            var children = [];

            data.items.forEach(function (row) {
                children.push(this.getRowTemplate(row));
            }, this);

            layout(children);
        },

        /**
         * Clears the filters if entity type is changed.
         *
         * @param {String} entity
         */
        onEntityUpdate: function (entity) { //eslint-disable-line no-unused-vars
            this.elems().forEach(function (elem) {
                if (_.isFunction(elem.clear)) {
                    elem.clear();
                }
            });

            this.filters = {};
        }
    });
});
