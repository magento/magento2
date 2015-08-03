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
            rowButtonsTmpl: 'ui/grid/editing/row-buttons',
            headerButtonsTmpl: 'ui/grid/editing/header-buttons',
            errorsCount: 0,
            isMultiEditing: false,
            isSingleEditing: false,
            rowsData: [],
            templates: {
                record: {
                    parent: '${ $.$data.editor.name }',
                    name: '${ $.$data.recordId }',
                    component: 'Magento_Ui/js/grid/editing/record',
                    columnsProvider: '${ $.$data.editor.columnsProvider }',
                    active: true
                }
            },
            viewConfig: {
                component: 'Magento_Ui/js/grid/editing/editor-view',
                name: '${ $.name }_view',
                model: '${ $.name }',
                columnsProvider: '${ $.columnsProvider }'
            },
            imports: {
                rowsData: '${ $.columnsProvider }:rows',
                onRowsDataChange: 'rowsData'
            },
            listens: {
                elems: 'onEditingChange',
                '${ $.selectProvider }:selected': 'onSelectionsChange'
            },
            modules: {
                columns: '${ $.columnsProvider }',
                selections: '${ $.selectProvider }'
            }
        },

        /**
         * Initializes editor component.
         *
         * @returns {Editor} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'updateState', 'countErros');

            this._super()
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
                .observe([
                    'errorsCount',
                    'isMultiEditing',
                    'isSingleEditing'
                ]);

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
         *
         * @returns {Editor} Chainable.
         */
        initElement: function (record) {
            record.on({
                'active': this.updateState,
                'errorsCount': this.countErros
            });

            return this._super();
        },

        /**
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Editor} Chainable.
         */
        createRecord: function (id, isIndex) {
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
            record.rowData = this.getRowData(id);

            layout([record]);

            return this;
        },

        /**
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Editor} Chainable.
         */
        edit: function (id, isIndex, ignoreSelections) {
            var recordId    = this.getId(id, isIndex),
                record      = this.getRecord(recordId);

            if (!this.hasActive() && !ignoreSelections) {
                this.selections('deselectAll');
                this.selections('select', recordId);
            }

            record ?
                record.active(true) :
                this.createRecord(recordId);

            return this;
        },

        /**
         * Hides specified records and resets theirs data.
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Editor} Chainable.
         */
        cancel: function (id, isIndex) {
            this.hide(id, isIndex)
                .reset(id, isIndex);

            return this;
        },

        /**
         * Hides specified records.
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Editor} Chainable.
         */
        hide: function (id, isIndex) {
            var record = this.getRecord(id, isIndex);

            !record ?
                this.elems.each('active', false) :
                record.active(false);

            return this;
        },

        /**
         * Resets data of a specified records.
         *
         * @returns {Editor} Chainable.
         */
        reset: function () {
            var id;

            this.rowsData.forEach(function (rowData) {
                id = rowData[this.indexField];

                this.setRecordData(id, rowData);
            }, this);

            return this;
        },

        /**
         *
         * @returns {Editor} Chainable.
         */
        save: function () {
            this.validate();

            return this;
        },

        /**
         *
         * @returns {Editor} Chainable.
         */
        validate: function () {
            this.getActive().forEach(function (record) {
                record.validate();
            });

            return this;
        },

        /**
         *
         * @param {(Number|String)} id - Records' identifier or its' index in the rows array.
         * @param {Boolean} [isIndex=false] - Flag that indicates if first
         *      parameter is index or identifier.
         * @returns {Number|String} Records' id.
         */
        getId: function (id, isIndex) {
            var rowsData = this.rowsData,
                record;

            if (isIndex === true) {
                record = rowsData[id];
                id = record ? record[this.indexField] : false;
            }

            return id;
        },

        /**
         * Returns specified record.
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Record}
         */
        getRecord: function (id, isIndex) {
            return this.elems.findWhere({
                recordId: this.getId(id, isIndex)
            });
        },

        /**
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {String}
         */
        formRecordName: function (id, isIndex) {
            id = this.getId(id, isIndex);

            return this.name + '.' + id;
        },

        /**
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Object} data - Records' data.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Editor} Chainable.
         */
        setRecordData: function (id, data, isIndex) {
            var record = this.getRecord(id, isIndex);

            if (record) {
                record.setData(data);
            }

            return this;
        },

        /**
         *
         * @returns {Object}
         */
        getRowData: function (id) {
            return _.find(this.rowsData, function (row) {
                return row[this.indexField] === id;
            }, this);
        },

        /**
         * Checks if specified record is active.
         *
         * @param {(Number|String)} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Boolean}
         */
        isActive: function (id, isIndex) {
            var record = this.getRecord(id, isIndex);

            return record && record.active();
        },

        /**
         * Returns array of currently active records.
         *
         * @returns {Array}
         */
        getActive: function () {
            return this.elems.filter('active');
        },

        /**
         * Checks if editor has active records.
         *
         * @returns {Boolean}
         */
        hasActive: function () {
            return !!this.countActive();
        },

        /**
         * Counts number of active records.
         *
         * @returns {Number}
         */
        countActive: function () {
            return this.getActive().length;
        },

        /**
         * Counts number of invalid fields accros all active records.
         *
         * @returns {Number}
         */
        countErros: function () {
            var errorsCount = 0;

            this.getActive().forEach(function (record) {
                errorsCount += record.errorsCount();
            });

            this.errorsCount(errorsCount);

            return errorsCount;
        },

        /**
         * Checks if editor has any errors.
         *
         * @returns {Boolean}
         */
        hasErrors: function () {
            return this.countErros() > 0;
        },

        /**
         * Defines values of the 'isMultiEditing' and
         * 'isSingleEditing' properties.
         *
         * @returns {Editor} Chainable.
         */
        updateState: function () {
            var activeRecords = this.countActive();

            this.isMultiEditing(activeRecords > 1);
            this.isSingleEditing(activeRecords === 1);

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
                    this.hide(record.recordId);
                }
            }, this);

            selections.forEach(function (id) {
                this.edit(id, false, true);
            }, this);

            return this;
        },

        /**
         * Listener of the records 'active' property.
         */
        onEditingChange: function () {
            this.updateState();
        },

        /**
         * Listener of the multiselect selections data.
         */
        onSelectionsChange: function () {
            if (!this.hasActive()) {
                return;
            }

            this.editSelected();
        },

        /**
         * Listener of the litings' rows data.
         */
        onRowsDataChange: function () {
            this.hide();
        }
    });
});
