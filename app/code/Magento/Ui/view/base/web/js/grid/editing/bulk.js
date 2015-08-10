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
            listens: {
                data: 'onDataChange',
                enabled: 'onEnableChange',
                '${ $.editorProvider }:isMultiEditing': 'onMultiEditing'
            },
            modules: {
                editor: '${ $.editorProvider }'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Bulk} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe({
                    hasData: false,
                    enabled: false
                });

            return this;
        },

        /**
         * Extends original method to disable possible
         * 'required-entry' validation rule.
         *
         * @returns {Object} Columns' editor definition.
         */
        buildEditor: function () {
            var editor = this._super(),
                rules = editor.validation;

            if (rules) {
                delete rules['required-entry'];
            }

            editor.disabled = false;

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

            this.applyData()
                .clear();

            return this;
        },

        /**
         * Sets available data to all active records.
         *
         * @param {Object} [data] -  If not specified, then current fields data will be used.
         * @returns {Bulk} Chainable.
         */
        applyData: function (data) {
            data = data || this.getData();

            this.editor('setRecordsData', data, true);

            return this;
        },

        /**
         * Returns data of all non-empty fields.
         *
         * @returns {Object} Fields data without empty values.
         */
        getData: function () {
            return removeEmpty(this.data);
        },

        /**
         * Updates own 'hasData' property and defines
         * whether regular rows editing can be resumed.
         *
         * @returns {Bulk} Chainable.
         */
        updateState: function () {
            var fields  = _.keys(this.getData()),
                hasData = !!fields.length;

            this.hasData(hasData);

            if (!this.enabled()) {
                fields = [];
            }

            this.editor('disableFields', fields);
            this.editor('canSave', !fields.length);

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
         * Listener of the 'data' object changes.
         */
        onDataChange: function () {
            this.updateState();
        },

        /**
         * Listener of the 'enabled' property.
         */
        onEnableChange: function () {
            this.updateState();
        },

        /**
         * Listener of the editors' multiediting state.
         *
         * @param {Boolean} enabled - Whether multiediting is enabled.
         */
        onMultiEditing: function (enabled) {
            this.enabled(enabled);
        }
    });
});
