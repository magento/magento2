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
         *
         * @returns {Record} Chainable.
         */
        createEditor: function (column) {
            var editors = this.templates.editors,
                editor  = this.getColumnEditor(column);

            editor = utils.extend({}, editors.base, editor);
            editor = utils.template(editor, {
                record: this,
                column: column
            }, true, true);

            editor.visible = column.visible;

            layout([editor]);

            return this;
        },

        /**
         *
         * @returns {Object}
         */
        getColumnEditor: function (column) {
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

            return editor;
        },

        /**
         *
         * @returns {Record} Chainable.
         */
        createEditors: function (columns) {
            columns.forEach(function (column) {
                if (column.editor && !this.getEditor(column.index)) {
                    this.createEditor(column);
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
         *
         * @returns {Object}
         */
        getEditor: function (index) {
            return this.elems.findWhere({
                index: index
            });
        },

        /**
         *
         * @returns {Record} Chainable.
         */
        setData: function (data) {
            this.set('data', utils.copy(data));

            return this;
        },

        /**
         *
         */
        validate: function () {
            return this.elems.map('validate');
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
         *
         */
        onColumnsUpdate: function (columns) {
            this.createEditors(columns)
                .updateFields();
        }
    });
});
