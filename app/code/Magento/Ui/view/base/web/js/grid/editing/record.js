/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiCollection'
], function (_, utils, layout, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            active: true,
            hasChanges: false,
            fields: [],
            errorsCount: 0,
            fieldTmpl: 'ui/grid/editing/field',
            rowTmpl: 'ui/grid/editing/row',
            templates: {
                fields: {
                    base: {
                        parent: '${ $.$data.record.name }',
                        name: '${ $.$data.column.index }',
                        provider: '${ $.$data.record.name }',
                        dataScope: 'data.${ $.$data.column.index }',
                        imports: {
                            disabled: '${ $.$data.record.parentName }:fields.${ $.$data.column.index }.disabled'
                        },
                        isEditor: true
                    },
                    text: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'ui/form/element/input'
                    },
                    date: {
                        component: 'Magento_Ui/js/form/element/date',
                        template: 'ui/form/element/date',
                        dateFormat: 'MMM d, y h:mm:ss a'
                    },
                    select: {
                        component: 'Magento_Ui/js/form/element/select',
                        template: 'ui/form/element/select',
                        options: '${ JSON.stringify($.$data.column.options) }'
                    }
                }
            },
            listens: {
                elems: 'updateFields',
                data: 'updateState'
            },
            imports: {
                onColumnsUpdate: '${ $.columnsProvider }:elems'
            },
            modules: {
                columns: '${ $.columnsProvider }',
                editor: '${ $.editorProvider }'
            }
        },

        /**
         * Initializes record component.
         *
         * @returns {Record} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'countErrors');
            utils.limit(this, 'updateState', 10);

            return this._super();
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Record} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('errorsCount hasChanges')
                .observe('active fields');

            return this;
        },

        /**
         * Adds listeners on a field.
         *
         * @returns {Record} Chainable.
         */
        initElement: function (field) {
            field.on('error', this.countErrors);

            return this._super();
        },

        /**
         * Creates new instance of a field.
         *
         * @param {Column} column - Column instance which contains field definition.
         * @returns {Record} Chainable.
         */
        initField: function (column) {
            var field = this.buildField(column);

            layout([field]);

            return this;
        },

        /**
         * Builds fields' configuration described in a provided column.
         *
         * @param {Column} column - Column instance which contains field definition.
         * @returns {Object} Complete fields' configuration.
         */
        buildField: function (column) {
            var fields = this.templates.fields,
                field  = column.editor;

            if (_.isObject(field) && field.editorType) {
                field = utils.extend({}, fields[field.editorType], field);
            } else if (_.isString(field)) {
                field = fields[field];
            }

            field = utils.extend({}, fields.base, field);

            return utils.template(field, {
                record: this,
                column: column
            }, true, true);
        },

        /**
         * Creates fields for the specfied columns.
         *
         * @param {Array} columns - An array of column instances.
         * @returns {Record} Chainable.
         */
        createFields: function (columns) {
            columns.forEach(function (column) {
                if (column.editor && !this.hasChild(column.index)) {
                    this.initField(column);
                }
            }, this);

            return this;
        },

        /**
         * Returns instance of a column found by provided index.
         *
         * @param {String} index - Index of a column (e.g. 'title').
         * @returns {Column}
         */
        getColumn: function (index) {
            return this.columns().getChild(index);
        },

        /**
         * Returns records' current data object.
         *
         * @returns {Object}
         */
        getData: function () {
            return this.filterData(this.data);
        },

        /**
         * Returns saved records' data. Data will be processed
         * with a 'filterData' and 'normalizeData' methods.
         *
         * @returns {Object} Saved records' data.
         */
        getSavedData: function () {
            var editor      = this.editor(),
                savedData   = editor.getRowData(this.index);

            savedData = this.filterData(savedData);

            return this.normalizeData(savedData);
        },

        /**
         * Replaces current records' data with the provided one.
         *
         * @param {Object} data - New records data.
         * @param {Boolean} [partial=false] - Flag that defines whether
         *      to completely replace current data or to extend it.
         * @returns {Record} Chainable.
         */
        setData: function (data, partial) {
            var currentData = partial ? this.data : {};

            data = this.normalizeData(data);
            data = utils.extend({}, currentData, data);

            this.set('data', data);

            return this;
        },

        /**
         * Filters provided object extracting from it values
         * that can be matched with an existing fields.
         *
         * @param {Object} data - Object to be processed.
         * @returns {Object}
         */
        filterData: function (data) {
            var fields = _.pluck(this.elems(), 'index');

            _.each(this.preserveFields, function (enabled, field) {
                if (enabled && !_.contains(fields, field)) {
                    fields.push(field);
                }
            });

            return _.pick(data, fields);
        },

        /**
         * Parses values of a provided object with
         * a 'normalizeData' method of a corresponding field.
         *
         * @param {Object} data - Data to be processed.
         * @returns {Object}
         */
        normalizeData: function (data) {
            var index;

            this.elems.each(function (elem) {
                index = elem.index;

                if (data.hasOwnProperty(index)) {
                    data[index] = elem.normalizeData(data[index]);
                }
            });

            return data;
        },

        /**
         * Clears values of all fields.
         *
         * @returns {Record} Chainable.
         */
        clear: function () {
            this.elems.each('clear');

            return this;
        },

        /**
         * Validates all of the available fields.
         *
         * @returns {Array} An array with validatation results.
         */
        validate: function () {
            return this.elems.map('validate');
        },

        /**
         * Checks if all fields are valid.
         *
         * @returns {Boolean}
         */
        isValid: function () {
            return _.every(this.validate(), 'valid');
        },

        /**
         * Counts total errors ammount accros all fields.
         *
         * @returns {Number}
         */
        countErrors: function () {
            var errorsCount = this.elems.filter('error').length;

            this.errorsCount = errorsCount;

            return errorsCount;
        },

        /**
         * Returns difference between current data and its'
         * initial state, retrieved from the records collection.
         *
         * @returns {Object} Object with changes descriptions.
         */
        checkChanges: function () {
            var savedData   = this.getSavedData(),
                data        = this.getData();

            return utils.compare(savedData, data);
        },

        /**
         * Updates 'fields' array filling it with available edtiors
         * or with column instances if associated field is not present.
         *
         * @returns {Record} Chainable.
         */
        updateFields: function () {
            var fields;

            fields = this.columns().elems.map(function (column) {
                return this.getChild(column.index) || column;
            }, this);

            this.fields(fields);

            return this;
        },

        /**
         * Updates state of a 'hasChanges' property.
         *
         * @returns {Record} Chainable.
         */
        updateState: function () {
            var diff = this.checkChanges();

            this.hasChanges = !diff.equal;

            return this;
        },

        /**
         * Checks if provided column is an actions column.
         *
         * @param {Column} column - Column to be checked.
         * @returns {Boolean}
         */
        isActionsColumn: function (column) {
            return column.dataType === 'actions';
        },

        /**
         * Listener of columns provider child array changes.
         *
         * @param {Array} columns - Modified child elements array.
         */
        onColumnsUpdate: function (columns) {
            this.createFields(columns)
                .updateFields();
        }
    });
});
