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
    'uiLayout',
    'mage/translate',
    'uiCollection'
], function (_, utils, layout, $t, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            rowButtonsTmpl: 'ui/grid/editing/row-buttons',
            headerButtonsTmpl: 'ui/grid/editing/header-buttons',
            successMsg: $t('You have successfully saved your edits.'),
            errorsCount: 0,
            bulkEnabled: true,
            multiEditingButtons: true,
            singleEditingButtons: true,
            isMultiEditing: false,
            isSingleEditing: false,
            permanentlyActive: false,
            rowsData: [],
            fields: {},

            templates: {
                record: {
                    parent: '${ $.$data.editor.name }',
                    name: '${ $.$data.recordId }',
                    component: 'Magento_Ui/js/grid/editing/record',
                    columnsProvider: '${ $.$data.editor.columnsProvider }',
                    editorProvider: '${ $.$data.editor.name }',
                    preserveFields: {
                        '${ $.$data.editor.indexField }': true
                    }
                }
            },
            bulkConfig: {
                component: 'Magento_Ui/js/grid/editing/bulk',
                name: '${ $.name }_bulk',
                editorProvider: '${ $.name }',
                columnsProvider: '${ $.columnsProvider }'
            },
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },
            viewConfig: {
                component: 'Magento_Ui/js/grid/editing/editor-view',
                name: '${ $.name }_view',
                model: '${ $.name }',
                columnsProvider: '${ $.columnsProvider }'
            },
            imports: {
                rowsData: '${ $.dataProvider }:data.items'
            },
            listens: {
                '${ $.dataProvider }:reloaded': 'cancel',
                '${ $.selectProvider }:selected': 'onSelectionsChange'
            },
            modules: {
                source: '${ $.dataProvider }',
                client: '${ $.clientConfig.name }',
                columns: '${ $.columnsProvider }',
                bulk: '${ $.bulkConfig.name }',
                selections: '${ $.selectProvider }'
            }
        },

        /**
         * Initializes editor component.
         *
         * @returns {Editor} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'updateState', 'countErrors', 'onDataSaved', 'onSaveError');

            this._super()
                .initBulk()
                .initClient()
                .initView();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Editor} Chainable.
         */
        initObservable: function () {
            this._super()
                .track([
                    'errorsCount',
                    'isMultiEditing',
                    'isSingleEditing',
                    'isSingleColumnEditing',
                    'changed'
                ])
                .observe({
                    canSave: true,
                    activeRecords: [],
                    messages: []
                });

            return this;
        },

        /**
         * Initializes bulk editing component.
         *
         * @returns {Editor} Chainable.
         */
        initBulk: function () {
            if (this.bulkEnabled) {
                layout([this.bulkConfig]);
            }

            return this;
        },

        /**
         * Initializes editors' view component.
         *
         * @returns {Editor} Chainable.
         */
        initView: function () {
            layout([this.viewConfig]);

            return this;
        },

        /**
         * Initializes client component.
         *
         * @returns {Editor} Chainable.
         */
        initClient: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Creates instance of a new record.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        initRecord: function (id, isIndex) {
            var record = this.buildRecord(id, isIndex);

            layout([record]);

            return this;
        },

        /**
         * Adds listeners on a new record.
         *
         * @param {Record} record
         * @returns {Editor} Chainable.
         */
        initElement: function (record) {
            record.on({
                'active': this.updateState,
                'errorsCount': this.countErrors
            });

            this.updateState();

            return this._super();
        },

        /**
         * Creates configuration for a new record associated with a row data.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Object} Record configuration.
         */
        buildRecord: function (id, isIndex) {
            var recordId = this.getId(id, isIndex),
                recordTmpl = this.templates.record,
                record;

            if (this.getRecord(recordId)) {
                return this;
            }

            record = utils.template(recordTmpl, {
                editor: this,
                recordId: id
            });

            record.recordId = id;
            record.data     = this.getRowData(id);

            return record;
        },

        /**
         * Starts editing of a specified record. If records'
         * instance doesn't exist, than it will be created.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        edit: function (id, isIndex) {
            var recordId = this.getId(id, isIndex),
                record   = this.getRecord(recordId);

            record ?
                record.active(true) :
                this.initRecord(recordId);

            return this;
        },

        /**
         * Drops list of selections while activating only the specified record.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        startEdit: function (id, isIndex) {
            var recordId = this.getId(id, isIndex);

            this.selections()
                .deselectAll()
                .select(recordId);

            return this.edit(recordId);
        },

        /**
         * Hides records and resets theirs data.
         *
         * @returns {Editor} Chainable.
         */
        cancel: function () {
            this.reset()
                .hide()
                .clearMessages()
                .bulk('clear');

            return this;
        },

        /**
         * Hides records.
         *
         * @returns {Editor} Chainable.
         */
        hide: function () {
            this.activeRecords.each('active', false);

            return this;
        },

        /**
         * Resets active records.
         *
         * @returns {Editor} Chainable.
         */
        reset: function () {
            this.elems.each(function (record) {
                this.resetRecord(record.recordId);
            }, this);

            return this;
        },

        /**
         * Validates and saves data of active records.
         *
         * @returns {Editor} Chainable.
         */
        save: function () {
            var data;

            if (!this.isValid()) {
                return this;
            }

            data = {
                items: this.getData()
            };

            this.clearMessages()
                .columns('showLoader');

            this.client()
                .save(data)
                .done(this.onDataSaved)
                .fail(this.onSaveError);

            return this;
        },

        /**
         * Validates all active records.
         *
         * @returns {Array} An array of records and theirs validation results.
         */
        validate: function () {
            return this.activeRecords.map(function (record) {
                return {
                    target: record,
                    valid: record.isValid()
                };
            });
        },

        /**
         * Checks if all active records are valid.
         *
         * @returns {Boolean}
         */
        isValid: function () {
            return _.every(this.validate(), 'valid');
        },

        /**
         * Returns active records data, indexed by a theirs ids.
         *
         * @returns {Object} Collection of records data.
         */
        getData: function () {
            var data = this.activeRecords.map('getData');

            return _.indexBy(data, this.indexField);
        },

        /**
         * Sets provided data to all active records.
         *
         * @param {Object} data - See 'setData' method of a 'Record'.
         * @param {Boolean} partial - See 'setData' method of a 'Record'.
         * @returns {Editor} Chainable.
         */
        setData: function (data, partial) {
            this.activeRecords.each('setData', data, partial);

            return this;
        },

        /**
         * Resets specific records' data
         * to the data present in associated row.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        resetRecord: function (id, isIndex) {
            var record  = this.getRecord(id, isIndex),
                data    = this.getRowData(id, isIndex);

            if (record && data) {
                record.setData(data);
            }

            return this;
        },

        /**
         * Returns instance of a specified record.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Record}
         */
        getRecord: function (id, isIndex) {
            return this.elems.findWhere({
                recordId: this.getId(id, isIndex)
            });
        },

        /**
         * Creates record name based on a provided id.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {String}
         */
        formRecordName: function (id, isIndex) {
            id = this.getId(id, isIndex);

            return this.name + '.' + id;
        },

        /**
         * Disables editing of specified fields.
         *
         * @param {Array} fields - An array of fields indexes to be disabled.
         * @returns {Editor} Chainable.
         */
        disableFields: function (fields) {
            var columns = this.columns().elems(),
                data    = utils.copy(this.fields);

            columns.forEach(function (column) {
                var index = column.index,
                    field = data[index] = data[index] || {};

                field.disabled = _.contains(fields, index);
            });

            this.set('fields', data);

            return this;
        },

        /**
         * Converts index of a row into the record id.
         *
         * @param {(Number|String)} id - Records' identifier or its' index in the rows array.
         * @param {Boolean} [isIndex=false] - Flag that indicates if first
         *      parameter is an index or identifier.
         * @returns {String} Records' id.
         */
        getId: function (id, isIndex) {
            var rowsData = this.rowsData,
                record;

            if (isIndex === true) {
                record  = rowsData[id];
                id      = record ? record[this.indexField] : false;
            }

            return id;
        },

        /**
         * Returns data of a specified row.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Object}
         */
        getRowData: function (id, isIndex) {
            id = this.getId(id, isIndex);

            return _.find(this.rowsData, function (row) {
                return row[this.indexField] === id;
            }, this);
        },

        /**
         * Checks if specified record is active.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See'getId' method.
         * @returns {Boolean}
         */
        isActive: function (id, isIndex) {
            var record = this.getRecord(id, isIndex);

            return _.contains(this.activeRecords(), record);
        },

        /**
         * Checks if editor has active records.
         *
         * @returns {Boolean}
         */
        hasActive: function () {
            return !!this.activeRecords().length || this.permanentlyActive;
        },

        /**
         * Counts number of active records.
         *
         * @returns {Number}
         */
        countActive: function () {
            return this.activeRecords().length;
        },

        /**
         * Counts number of invalid fields across all active records.
         *
         * @returns {Number}
         */
        countErrors: function () {
            var errorsCount = 0;

            this.activeRecords.each(function (record) {
                errorsCount += record.errorsCount;
            });

            this.errorsCount = errorsCount;

            return errorsCount;
        },

        /**
         * Translatable error message text.
         *
         * @returns {String}
         */
        countErrorsMessage: function () {
            return $t('There are {placeholder} messages requires your attention.')
                .replace('{placeholder}', this.countErrors());
        },

        /**
         * Checks if editor has any errors.
         *
         * @returns {Boolean}
         */
        hasErrors: function () {
            return !!this.countErrors();
        },

        /**
         * Handles changes of the records 'active' property.
         *
         * @returns {Editor} Chainable.
         */
        updateState: function () {
            var active      = this.elems.filter('active'),
                activeCount = active.length,
                columns     = this.columns().elems;

            columns.each('disableAction', !!activeCount);

            this.isMultiEditing = activeCount > 1;
            this.isSingleEditing = activeCount === 1;

            this.activeRecords(active);

            return this;
        },

        /**
         * Returns list of selections from a current page.
         *
         * @returns {Array}
         */
        getSelections: function () {
            return this.selections().getPageSelections();
        },

        /**
         * Starts editing of selected records. If record
         * is not in the selections list, then it will get hidden.
         *
         * @returns {Editor} Chainable.
         */
        editSelected: function () {
            var selections = this.getSelections();

            this.elems.each(function (record) {
                if (!_.contains(selections, record.recordId)) {
                    record.active(false);
                }
            });

            selections.forEach(function (id) {
                this.edit(id);
            }, this);

            return this;
        },

        /**
         * Checks if there is any additional messages.
         *
         * @returns {Boolean}
         */
        hasMessages: function () {
            return this.messages().length;
        },

        /**
         * Adds new additional message or a set of messages.
         *
         * @param {(Object|Array)} message - Messages to be added.
         * @returns {Editor} Chainable.
         */
        addMessage: function (message) {
            var messages = this.messages();

            Array.isArray(message) ?
                messages.push.apply(messages, message) :
                messages.push(message);

            this.messages(messages);

            return this;
        },

        /**
         * Removes all additional messages.
         *
         * @returns {Editor} Chainable.
         */
        clearMessages: function () {
            this.messages.removeAll();

            return this;
        },

        /**
         * Listener of the selections data changes.
         */
        onSelectionsChange: function () {
            if (this.hasActive()) {
                this.editSelected();
            }
        },

        /**
         * Handles successful save request.
         */
        onDataSaved: function () {
            var msg = {
                type: 'success',
                message: this.successMsg
            };

            this.addMessage(msg)
                .source('reload', {
                    refresh: true
                });
        },

        /**
         * Handles failed save request.
         *
         * @param {(Array|Object)} errors - List of errors or a single error object.
         */
        onSaveError: function (errors) {
            this.addMessage(errors)
                .columns('hideLoader');
        }
    });
});
