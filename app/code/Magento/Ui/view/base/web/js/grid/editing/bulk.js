define([
    'underscore',
    'mageUtils',
    './record'
], function (_, utils, Record) {
    'use strict';

    /**
     * Removes empty properties from the provided object.
     *
     * @param {Object} data - Object to be processed.
     * @returns {Object}
     */
    function removeEmpty(data) {
        data = utils.flatten(data);
        data = _.omit(data, utils.isEmpty);

        return utils.unflatten(data);
    }

    return Record.extend({
        defaults: {
            template: 'ui/grid/editing/bulk',
            templates: {
                editors: {
                    select: {
                        caption: ' '
                    }
                }
            },
            modules: {
                editor: '${ $.editorProvider }'
            }
        },

        /**
         * Extends original method to disable possible
         * 'required-entry' validation rule.
         *
         * @returns {Object} Columns' editor definition.
         */
        getColumnEditor: function () {
            var editor = this._super(),
                rules = editor && editor.validation;

            if (rules) {
                delete rules['required-entry'];
            }

            return editor;
        },

        /**
         * Applies current data to all active records.
         *
         * @returns {Bulk} Chainable.
         */
        apply: function () {
            if (!this.isValid()) {
                return this;
            }

            this.applyData();

            return this;
        },

        /**
         * Sets available data to all active records.
         *
         * @param {Object} [data] -  If not specified, then current fields data will be used.
         * @returns {Bulk} Chainable.
         */
        applyData: function (data) {
            var editor = this.editor();

            data = data || this.getData();

            editor.getActive().forEach(function (record) {
                record.setData(data, true);
            });

            return this;
        },

        /**
         * Returns data of all non-empty fields.
         *
         * @returns {Object}
         */
        getData: function () {
            return removeEmpty(this.data);
        },

        /**
         * Checks if provided column is an actions column.
         *
         * @param {Column} column - Column to be checked.
         * @returns {Boolean}
         */
        isActionsColumn: function (column) {
            return column.dataType === 'actions';
        }
    });
});
