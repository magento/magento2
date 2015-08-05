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
         * Builds editors configuration described in a provided column.
         *
         * @param {Column} column - Column instance which contains editor definition.
         * @returns {Object}
         */
        buildColumnEditor: function (column) {
            var editors      = this.templates.editors,
                columnEditor = column.editor,
                editor;

            if (typeof columnEditor === 'object') {
                editor = editors[columnEditor.editorType];

                if (editor) {
                    editor = utils.extend({}, editor, columnEditor);
                }
            } else {
                editor = editors[columnEditor];
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
         *
         * @returns {Record} Chainable.
         */
        createEditors: function (columns) {
            var editor;

            columns.forEach(function (column) {
                if (column.editor && !this.getEditor(column.index)) {
                    editor = this.buildColumnEditor(column);

                    this.initEditor(editor);
                }
            }, this);

            return this;
        },

        /**
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

        getColumn: function (index) {
            return this.columns().getColumn(index);
        },

        /**
         * Replaces current records' data with the provided one.
         *
         * @param {Object} data
         * @param {Boolean} [partial=false]
         * @returns {Record} Chainable.
         */
        setData: function (data, partial) {
            var currentData = this.data,
                result;

            result = partial ?
                utils.extend({}, currentData, data) :
                utils.copy(data);

            this.set('data', result);

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
         * Clears values of all fields.
         *
         * @returns {Record} Chainable.
         */
        clear: function () {
            this.elems.each('clear');

            return this;
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
         * Listner of columns provider child array changes.
         *
         * @param {Array} columns - Modified child elements array.
         */
        onColumnsUpdate: function (columns) {
            this.createEditors(columns)
                .updateFields();
        }
    });
});
