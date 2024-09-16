/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'uiLayout',
    'rjsResolver'
], function (_, dynamicRowsGrid, layout, resolver) {
    'use strict';

    return dynamicRowsGrid.extend({
        defaults: {
            label: '',
            columnsHeader: false,
            columnsHeaderAfterRender: true,
            addButton: false,
            isDefaultFieldScope: 'is_default',
            defaultRecords: {
                use: [],
                moreThanOne: false,
                state: {}
            },
            listens: {
                inputType: 'onInputTypeChange',
                isDefaultValue: 'onIsDefaultValue',
                pageSize: 'onPageSizeChange'
            },
            sizesConfig: {
                component: 'Magento_Ui/js/grid/paging/sizes',
                name: '${ $.name }_sizes',
                options: {
                    '20': {
                        value: 20,
                        label: 20
                    },
                    '30': {
                        value: 30,
                        label: 30
                    },
                    '50': {
                        value: 50,
                        label: 50
                    },
                    '100': {
                        value: 100,
                        label: 100
                    },
                    '200': {
                        value: 200,
                        label: 200
                    }
                },
                storageConfig: {
                    provider: '${ $.storageConfig.provider }',
                    namespace: '${ $.storageConfig.namespace }'
                },
                enabled: false
            },
            links: {
                options: '${ $.sizesConfig.name }:options',
                pageSize: '${ $.sizesConfig.name }:value'
            },
            modules: {
                sizes: '${ $.sizesConfig.name }'
            }
        },

        /**
         * Initializes paging component.
         *
         * @returns {Paging} Chainable.
         */
        initialize: function () {
            this._super()
                .initSizes();

            return this;
        },

        /**
         * Initializes sizes component.
         *
         * @returns {Paging} Chainable.
         */
        initSizes: function () {
            if (this.sizesConfig.enabled) {
                layout([this.sizesConfig]);
            }

            return this;
        },

        /**
         * Handler for type select.
         *
         * @param {String} inputType - changed.
         */
        onInputTypeChange: function (inputType) {
            if (this.defaultRecords.moreThanOne && (inputType === 'radio' || inputType === 'select')) {
                _.each(this.defaultRecords.use, function (index, counter) {
                    this.source.set(
                        this.dataScope + '.bundle_selections.' + index + '.' + this.isDefaultFieldScope,
                        counter ? '0' : '1'
                    );
                }.bind(this));
            }
        },

        /**
         * Handler for is_default field.
         *
         * @param {Object} data - changed data.
         */
        onIsDefaultValue: function (data) {
            var cb,
                use = 0;

            this.defaultRecords.use = [];

            cb = function (elem, key) {

                if (~~elem) {
                    this.defaultRecords.use.push(key);
                    use++;
                }

                this.defaultRecords.moreThanOne = use > 1;
            }.bind(this);

            _.each(data, cb);
        },

        /**
         * Initialize elements from grid
         *
         * @param {Array} data
         *
         * @returns {Object} Chainable.
         */
        initElements: function (data) {
            var newData = this.getNewData(data);

            this.parsePagesData(data);

            if (newData.length) {
                if (this.insertData().length) {
                    this.parseProcessingAddChild(data, newData);
                }
            }

            return this;
        },

        /**
         * Mapping value from grid
         *
         * @param {Array} data
         */
        mappingValue: function (data) {
            if (_.isEmpty(data)) {
                return;
            }

            this._super();
        },

        /**
         * Handles changes of the page size.
         */
        onPageSizeChange: function () {
            resolver(function () {
                if (this.elems().length) {
                    this.reload();
                }
            }, this);
        },

        /**
         * Parse and processing the add child method to update the latest records if the record index is not a number.
         *
         * @param {Array} data
         * @param {Array} newData
         */
        parseProcessingAddChild: function (data, newData) {
            let recordIndex;

            recordIndex = data.length - newData.length - 1;
            if (!isNaN(recordIndex)) {
                _.each(newData, function (newRecord) {
                    this.processingAddChild(newRecord, ++recordIndex, newRecord[this.identificationProperty]);
                }, this);
            }
        }
    });
});
