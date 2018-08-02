/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid'
], function (_, dynamicRowsGrid) {
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
                isDefaultValue: 'onIsDefaultValue'
            }
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
            var newData = this.getNewData(data),
                recordIndex;

            this.parsePagesData(data);

            if (newData.length) {
                if (this.insertData().length) {
                    recordIndex = data.length - newData.length - 1;

                    _.each(newData, function (newRecord) {
                        this.processingAddChild(newRecord, ++recordIndex, newRecord[this.identificationProperty]);
                    }, this);
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
        }
    });
});
