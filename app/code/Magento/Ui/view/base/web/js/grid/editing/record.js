/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiComponent'
], function (_, utils, layout, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            fields: [],
            errorsCount: 0,
            fieldTmpl: 'ui/grid/editing/field',
            rowTmpl: 'ui/grid/editing/row',
            templates: {
                editors: {
                    base: {
                        parent: '${ $.$data.record.name }',
                        name: '${ $.$data.column.index }',
                        provider: '${ $.$data.record.name }',
                        dataScope: 'data.${ $.$data.column.index }',
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
                elems: 'updateFields'
            },
            imports: {
                onColumnsUpdate: '${ $.columnsProvider }:elems'
            },
            modules: {
                columns: '${ $.columnsProvider }'
            }
        },

        /**
         * Initializes record component.
         *
         * @returns {Record} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'countErrors');

            return this._super();
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Record} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('active fields errorsCount');

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
         * Creates new instance of an editor.
         *
         * @param {Object} editor - Editors' instance definition.
         * @returns {Record} Chainable.
         */
        initEditor: function (editor) {
            layout([editor]);

            return this;
        },

        /**
         * Builds editors' configuration described in a provided column.
         *
         * @param {Column} column - Column instance which contains editor definition.
         * @returns {Object} Complete editors' configuration.
         */
        buildEditor: function (column) {
            var editors = this.templates.editors,
                editor  = column.editor;

            if (typeof editor === 'object' && editor.editorType) {
                editor = utils.extend({}, editors[editor.editorType], editor);
            } else if (typeof editor == 'string') {
                editor = editors[editor];
            }

            editor = utils.extend({}, editors.base, editor);
            editor = utils.template(editor, {
                record: this,
                column: column
            }, true, true);

            editor.visible  = column.visible;
            editor.disabled = column.disabled;

            return editor;
        },

        /**
         * Creates editors for the specfied columns.
         *
         * @param {Array} columns - An array of column instances.
         * @returns {Record} Chainable.
         */
        createEditors: function (columns) {
            var editor;

            columns.forEach(function (column) {
                if (column.editor && !this.getEditor(column.index)) {
                    editor = this.buildEditor(column);

                    this.initEditor(editor);
                }
            }, this);

            return this;
        },

        /**
         * Returns instance of an editor found by provided identifier.
         *
         * @param {String} index - Identifier of an editor inside record.
         * @returns {Object}
         */
        getEditor: function (index) {
            return this.elems.findWhere({
                index: index
            });
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

            data = utils.extend({}, currentData, data);

            this.set('data', data);

            return this;
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
            var result = this.validate();

            return result.every(function (data) {
                return data.valid;
            });
        },

        /**
         * Counts total errors ammount accros all fields.
         *
         * @returns {Number}
         */
        countErrors: function () {
            var errorsCount = this.elems.filter('error').length;

            this.errorsCount(errorsCount);

            return errorsCount;
        },

        /**
         * Updates 'fields' array filling it with available edtiors
         * or with column instances if associated editor is not present.
         *
         * @returns {Record} Chainable.
         */
        updateFields: function () {
            var fields;

            fields = this.columns().elems.map(function (column) {
                return this.getEditor(column.index) || column;
            }, this);

            this.fields(fields);

            return this;
        },

        /**
         * Listener of columns provider child array changes.
         *
         * @param {Array} columns - Modified child elements array.
         */
        onColumnsUpdate: function (columns) {
            this.createEditors(columns)
                .updateFields();
        }
    });
});
