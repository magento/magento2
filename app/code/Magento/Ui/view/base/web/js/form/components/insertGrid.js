/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    './insert',
    'mageUtils',
    'underscore'
], function ($, Insert, utils, _) {
    'use strict';

    return Insert.extend({
        defaults: {
            behaviourType: 'simple',
            externalFilterMode: false,//excludeselectedItems
            externalCondition: 'nin',
            settings: {
                edit: {
                    imports: {
                        'onChangeRecord': '${ $.editorProvider }:changed'
                    }
                }
            },
            imports: {
                onSelectedChange: '${ $.selectionsProvider }:selected',
                updateUrl: '${ $.externalProvider }:update_url'
            },
            exports: {
                externalFiltersModifier: '${ $.externalProvider }:params.filters_modifier'
            },
            listens: {
                value: 'updateExternalFiltersModifier updateSelections'
            },
            modules: {
                selections: '${ $.selectionsProvider }'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            _.bindAll(this, 'updateValue', 'updateExternalValueByEditableData');

            return this;
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var defaults = this.constructor.defaults;

            if (config.behaviourType === 'edit') {
                defaults.editableData = {};
                _.map(defaults.settings.edit.imports, function (value, key) {
                    this.imports[key] = value;
                }, defaults);
            }

            return this._super();
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'externalValue'
                ]);
        },

        /**
         * Store data from edited record
         *
         * @param {Object} record
         */
        onChangeRecord: function (record) {
            this.updateEditableData(record);

            if (!this.dataLinks.imports) {
                return;
            }

            this.updateExternalValueByEditableData();
        },

        /**
         * Updates externalValue every time row is selected,
         * if it is configured by 'dataLinks.imports'
         *
         */
        onSelectedChange: function () {
            if (!this.dataLinks.imports || this.suppressDataLinks) {
                this.suppressDataLinks = false;

                return;
            }

            this.suppressDataLinks = true;
            this.updateExternalValue();
        },

        /**
         * Stores data from editor in editableData
         * @param {Object} record
         *
         */
        updateEditableData: function (record) {
            var id = utils.getKeys(record[0], true),
                value = record[0][id];

            this.editableData[id] = value;
        },

        /**
         * Updates externalValue by data from editor (already stored in editableData)
         *
         */
        updateExternalValueByEditableData: function () {
            var updatedExtValue;

            if (!this.behaviourType === 'edit' || _.isEmpty(this.editableData) || _.isEmpty(this.externalValue())) {
                return;
            }

            updatedExtValue = this.externalValue();
            updatedExtValue.map(function (item) {
                _.extend(item, this.editableData[item[item['id_field_name']]]);
            }, this);
            this.externalValue(updatedExtValue);
        },

        /**
         * Updates externalValue, from selectionsProvider data (if it is enough)
         * or ajax request to server
         *
         * @returns {Object} result - deferred that will be resolved when value is updated
         */
        updateExternalValue: function () {
            var result = $.Deferred(),
                provider = this.selections(),
                selections = provider && provider.getSelections(),
                itemsType = selections && selections.excludeMode ? 'excluded' : 'selected',
                index = provider && provider.indexField,
                rows = provider && provider.rows(),
                canUpdateFromSelection;

            if (!provider) {
                return result;
            }

            canUpdateFromSelection =
                itemsType === 'selected' &&
                _.intersection(_.pluck(rows, index), selections.selected).length ===
                selections.selected.length;

            if (canUpdateFromSelection) {
                this.updateFromSelectionData(selections, index, rows);
                this.updateExternalValueByEditableData();
                result.resolve();
            } else {
                this.updateFromServerData(selections, index, itemsType).done(function () {
                    this.updateExternalValueByEditableData();
                    result.resolve();
                }.bind(this));
            }

            return result;
        },

        /**
         * Updates externalValue, from selectionsProvider data
         *
         * @param {Object} selections
         * @param {Number} index
         * @param {Object} rows
         */
        updateFromSelectionData: function (selections, index, rows) {
            rows = selections.selected && selections.selected.length ?
                _.filter(rows, function (row) {
                    return _.contains(selections.selected, row[index]);
                }) : [];
            this.set('externalValue', rows);
        },

        /**
         * Updates externalValue, from ajax request to grab selected rows data
         *
         * @param {Object} selections
         * @param {Number} index
         * @param {String} itemsType
         *
         * @returns {Object} request - deferred that will be resolved when ajax is done
         */
        updateFromServerData: function (selections, index, itemsType) {
            var filterType = selections && selections.excludeMode ? 'nin' : 'in',
                selectionsData = {},
                request;

            selectionsData['filters_modifier'] = {};
            selectionsData['filters_modifier'][index] = {
                'condition_type': filterType,
                value: selections[itemsType]
            };

            _.extend(selectionsData, this.params || {}, selections.params);

            request = this.requestData(selectionsData);
            request
                .done(function (data) {
                    this.set('externalValue', data.items || data);
                    this.loading(false);
                }.bind(this))
                .fail(this.onError);

            return request;
        },

        /**
         * Updates external filter (if externalFilterMode is on)
         * every time, when value is updated,
         * so grid is re-filtered to exclude or include selected rows only
         *
         * @param {Object} items
         */
        updateExternalFiltersModifier: function (items) {
            var provider,
                index,
                filter = {};

            if (!this.externalFilterMode) {//|| !items || !items.length
                return;
            }

            provider = this.selections();
            index = provider && provider.indexField;
            filter[provider.indexField] = {
                'condition_type': this.externalCondition,
                value: _.pluck(items, index)
            };
            this.set('externalFiltersModifier', filter);
        },

        /**
         * Updates grid selections
         * every time, when extenalValue is updated,
         * so grid is re-selected according to externalValue updated
         *
         */
        updateSelections: function () {
            var provider = this.selections(),
                ids;

            if (!this.dataLinks.exports || this.suppressDataLinks) {
                this.suppressDataLinks = false;

                return;
            }

            this.suppressDataLinks = true;
            ids = _.pluck(this.value() || [], provider.indexField);
            provider.selected(ids || []);
        },

        /**
         * Updates value from external value
         *
         */
        updateValue: function () {
            this.set('value', this.externalValue());
        },

        /**
         * Updates external value, then updates value from external value
         *
         */
        save: function () {
            this.updateExternalValue().done(this.updateValue);
        }
    });
});
