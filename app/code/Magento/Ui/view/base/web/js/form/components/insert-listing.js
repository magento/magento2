/**
 * Copyright © 2016 Magento. All rights reserved.
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
            externalListingName: '${ $.ns }.${ $.ns }',
            behaviourType: 'simple',
            externalFilterMode: false,
            requestConfig: {
                method: 'POST'
            },
            externalCondition: 'nin',
            settings: {
                edit: {
                    imports: {
                        'onChangeRecord': '${ $.editorProvider }:changed'
                    }
                },
                filter: {
                    exports: {
                        'requestConfig': '${ $.externalProvider }:requestConfig'
                    }
                }
            },
            imports: {
                onSelectedChange: '${ $.selectionsProvider }:selected',
                'update_url': '${ $.externalProvider }:update_url',
                'indexField': '${ $.selectionsProvider }:indexField'
            },
            exports: {
                externalFiltersModifier: '${ $.externalProvider }:params.filters_modifier'
            },
            listens: {
                externalValue: 'updateExternalFiltersModifier updateSelections',
                indexField: 'initialUpdateListing'
            },
            modules: {
                selections: '${ $.selectionsProvider }',
                externalListing: '${ $.externalListingName }'
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

            if (config.externalFilterMode === true) {
                _.map(defaults.settings.filter.exports, function (value, key) {
                    this.exports[key] = value;
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

        /** @inheritdoc */
        destroyInserted: function () {
            if (this.isRendered && this.externalListing()) {
                this.externalListing().source.storage().clearRequests();
                this.externalListing().delegate('destroy');
            }

            return this._super();
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
         * Also suppress dataLinks so import/export of selections will not activate each other in circle
         *
         */
        onSelectedChange: function () {
            if (!this.dataLinks.imports ||
                this.suppressDataLinks ||
                _.isBoolean(this.initialExportDone) && !this.initialExportDone
            ) {
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
            var id = _.keys(record[0])[0];

            this.editableData[id] = record[0][id];
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
                _.extend(item, this.editableData[item[this.indexField]]);
            }, this);
            this.setExternalValue(updatedExtValue);
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
                selections,
                totalSelected,
                itemsType,
                rows;

            if (!provider) {
                return result;
            }

            selections = provider && provider.getSelections();
            totalSelected = provider.totalSelected();
            itemsType = selections && selections.excludeMode ? 'excluded' : 'selected';
            rows = provider && provider.rows();

            if (_.isEmpty(selections.selected)) {
                this.suppressDataLinks = false;

                return result;
            }

            if (this.canUpdateFromClientData(totalSelected, selections.selected, rows)) {
                this.updateFromClientData(selections.selected, rows);
                this.updateExternalValueByEditableData();
                result.resolve();
            } else {
                this.updateFromServerData(selections, itemsType).done(function () {
                    this.updateExternalValueByEditableData();
                    result.resolve();
                }.bind(this));
            }

            return result;
        },

        /**
         * Check if the selected rows data can be taken from selectionsProvider data
         * (which only stores data of the current page rows)
         *  + from already saved data
         *
         * @param {Boolean} totalSelected - total rows selected (include rows that were filtered out)
         * @param {Array} selected - ids of selected rows
         * @param {Object} rows
         */
        canUpdateFromClientData: function (totalSelected, selected, rows) {
            var alreadySavedSelectionsIds = _.pluck(this.externalValue(), this.indexField),
                rowsOnCurrentPageIds = _.pluck(rows, this.indexField);

            return totalSelected === selected.length &&
                _.intersection(_.union(alreadySavedSelectionsIds, rowsOnCurrentPageIds), selected).length ===
                selected.length;
        },

        /**
         * Updates externalValue, from selectionsProvider data
         * (which only stores data of the current page rows)
         *  + from already saved data
         *  so we can avoid request to server
         *
         * @param {Array} selected - ids of selected rows
         * @param {Object} rows
         */
        updateFromClientData: function (selected, rows) {
            var value,
                rowIds,
                valueIds;

            if (!selected || !selected.length) {
                this.setExternalValue([]);

                return;
            }

            value = this.externalValue();
            rowIds = _.pluck(rows, this.indexField);
            valueIds = _.pluck(value, this.indexField);

            value = _.map(selected, function (item) {
                if (_.contains(rowIds, item)) {
                    return _.find(rows, function (row) {
                        return row[this.indexField] === item;
                    }, this);
                } else if (_.contains(valueIds, item)) {
                    return _.find(value, function (row) {
                        return row[this.indexField] === item;
                    }, this);
                }
            }, this);

            this.setExternalValue(value);
        },

        /**
         * Updates externalValue, from ajax request to grab selected rows data
         *
         * @param {Object} selections
         * @param {String} itemsType
         *
         * @returns {Object} request - deferred that will be resolved when ajax is done
         */
        updateFromServerData: function (selections, itemsType) {
            var filterType = selections && selections.excludeMode ? 'nin' : 'in',
                selectionsData = {},
                request;

            _.extend(selectionsData, this.params || {}, selections.params);

            if (selections[itemsType] && selections[itemsType].length) {
                selectionsData.filters = {};
                selectionsData['filters_modifier'] = {};
                selectionsData['filters_modifier'][this.indexField] = {
                    'condition_type': filterType,
                    value: selections[itemsType]
                };
            }

            selectionsData.paging = {
                notLimits: 1
            };

            request = this.requestData(selectionsData, {
                method: this.requestConfig.method
            });
            request
                .done(function (data) {
                    this.setExternalValue(data.items || data);
                    this.loading(false);
                }.bind(this))
                .fail(this.onError);

            return request;
        },

        /**
         * Set listing rows data to the externalValue,
         * or if externalData is configured with the names of particular columns,
         * filter rows data to have only these columns, and then set to the externalValue
         *
         * @param {Object} newValue - rows data
         *
         */
        setExternalValue: function (newValue) {
            var keys = this.externalData,
                value = this.externalValue(),
                selectedIds = _.pluck(newValue, this.indexField);

            if (_.isArray(keys) && !_.isEmpty(keys)) {
                newValue = _.map(newValue, function (item) {
                    return _.pick(item, keys);
                }, this);
            } else if (keys && _.isString(keys) && !_.isEmpty(newValue)) {
                newValue = newValue[0][keys];
            }

            if (this.externalFilterMode) {
                newValue = _.union(newValue, _.filter(value,
                    function (item) {
                        return !_.contains(selectedIds, item[this.indexField]);
                    }, this));
            }

            this.set('externalValue', newValue);
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
                filter = {};

            if (!this.externalFilterMode) {
                return;
            }

            provider = this.selections();

            if (!provider) {
                this.needInitialListingUpdate = true;

                return;
            }

            filter[this.indexField] = {
                'condition_type': this.externalCondition,
                value: _.pluck(items, this.indexField)
            };
            this.set('externalFiltersModifier', filter);
        },

        /**
         * Updates grid selections
         * every time, when extenalValue is updated,
         * so grid is re-selected according to externalValue updated
         * Also suppress dataLinks so import/export of selections will not activate each other in circle
         *
         * @param {Object} items
         */
        updateSelections: function (items) {
            var provider,
                ids;

            if (!this.dataLinks.exports || this.suppressDataLinks) {
                this.suppressDataLinks = false;
                this.initialExportDone = true;

                return;
            }

            provider = this.selections();

            if (!provider) {
                this.needInitialListingUpdate = true;

                return;
            }

            this.suppressDataLinks = true;
            provider.deselectAll();

            if (_.isString(items)) {
                provider.selected([items] || []);
            } else {
                ids = _.pluck(items || [], this.indexField)
                    .map(function (item) {
                        return item.toString();
                    });

                provider.selected(ids || []);
            }
            this.initialExportDone = true;
        },

        /**
         * initial update of the listing
         * with rows that must be checked/filtered
         * by the indexes
         */
        initialUpdateListing: function () {
            var items = this.externalValue();

            if (this.needInitialListingUpdate && items) {
                this.updateExternalFiltersModifier(items);
                this.updateSelections(items);
                this.needInitialListingUpdate = false;
            }
        },

        /**
         * Reload source
         */
        reload: function () {
            this.externalSource().set('params.t', new Date().getTime());
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
            this.updateExternalValue().done(
                function () {
                    if (!this.realTimeLink) {
                        this.updateValue();
                    }
                }.bind(this)
            );
        }
    });
});
