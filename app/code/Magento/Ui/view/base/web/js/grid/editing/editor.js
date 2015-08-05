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
            bulkConfig: {
                component: 'Magento_Ui/js/grid/editing/bulk',
                name: '${ $.name }_bulk',
                editorProvider: '${ $.name }',
                columnsProvider: '${ $.columnsProvider }'
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
                '${ $.dataProvider }:reload': 'hide',
                '${ $.selectProvider }:selected': 'onSelectionsChange'
            },
            modules: {
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
            _.bindAll(this, 'updateState', 'countErros');

            this._super()
                .initBulk()
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
         * Initializes bulk editing component.
         *
         * @returns {Editor} Chainable.
         */
        initBulk: function () {
            layout([this.bulkConfig]);

            return this;
        },

        /**
         * Adds listeners on a new recrod.
         *
         * @param {Record} record
         * @returns {Editor} Chainable.
         */
        initElement: function (record) {
            record.on({
                'active': this.updateState,
                'errorsCount': this.countErros
            });

            this.updateState();

            return this._super();
        },

        /**
         * Creates new records' instance associated with a row data.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
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
            record.data     = this.getRowData(id);

            layout([record]);

            return this;
        },

        /**
         * Starts editing of a specfied record. If record doesn't
         * exist, than it will be created.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
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
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        cancel: function (id, isIndex) {
            this.reset(id, isIndex)
                .hide(id, isIndex);

            return this;
        },

        /**
         * Hides specified records.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
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
         * Resets data of specified records.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        reset: function (id, isIndex) {
            id = this.getId(id, isIndex);

            if (id !== false) {
                this.resetRecord(id);
            }

            this.getActive().forEach(function (record) {
                this.resetRecord(record.recordId);
            }, this);

            return this;
        },

        /**
         * STUBBED action.
         *
         * @returns {Editor} Chainable.
         */
        save: function () {
            this.validate();

            return this;
        },

        /**
         * STUBBED action.
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
         * Converts index of a row into the record id.
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
         * Sets provided data to the record.
         *
         * @param {Object} data - Records' data.
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        setRecordData: function (data, id, isIndex) {
            var record = this.getRecord(id, isIndex);

            if (record) {
                record.setData(data);
            }

            return this;
        },

        /**
         * Resets specific records' data
         * to the data present in asscotiated row.
         *
         * @param {(Number|String)} id - See 'getId' method.
         * @param {Boolean} [isIndex=false] - See 'getId' method.
         * @returns {Editor} Chainable.
         */
        resetRecord: function (id, isIndex) {
            var data = this.getRowData(id, isIndex);

            this.setRecordData(data, id, isIndex);

            return this;
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
                    record.active(false);
                }
            });

            selections.forEach(function (id) {
                this.edit(id, false, true);
            }, this);

            return this;
        },

        /**
         * Listener of the multiselect selections data.
         */
        onSelectionsChange: function () {
            if (!this.hasActive()) {
                return;
            }

            this.editSelected();
        }
    });
});
