/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
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
            active: false,
            templates: {
                fields: {
                    select: {
                        caption: ' '
                    }
                }
            },
            imports: {
                active: '${ $.editorProvider }:isMultiEditing'
            },
            listens: {
                data: 'updateState',
                active: 'updateState'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Bulk} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    hasData: false
                });

            return this;
        },

        /**
         * Extends original method to disable possible
         * 'required-entry' validation rule.
         *
         * @returns {Object} Columns' field definition.
         */
        buildField: function () {
            var field = this._super(),
                rules = field.validation;

            if (rules) {
                delete rules['required-entry'];
            }

            return field;
        },

        /**
         * Applies current data to all active records.
         *
         * @returns {Bulk} Chainable.
         */
        apply: function () {
            if (this.isValid()) {
                this.applyData()
                    .clear();
            }

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

            this.editor('setData', data, true);

            return this;
        },

        /**
         * Returns data of all non-empty fields.
         *
         * @returns {Object} Fields data without empty values.
         */
        getData: function () {
            return removeEmpty(this._super());
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

            this.hasData = hasData;

            if (!this.active()) {
                fields = [];
            }

            this.editor('disableFields', fields);
            this.editor('canSave', !fields.length);

            return this;
        }
    });
});
