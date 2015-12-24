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
            externalCondition: 'nin',
            externalFilter: {
                'condition_type': '${ $.externalCondition }',
                value: []
            },
            settings: {
                edit: {
                    listens: {
                        '${ $.editorProvider }:changed': 'onChangeRecord'
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
                value: 'updateExternalFiltersModifier'
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
            _.bindAll(this, 'updateValue');

            return this;
        },

        onChangeRecord: function (record) {
            var id = utils.getKeys(record[0], true),
                value = record[0][id],
                idName = value['id_field_name'],
                index;

            index = _.findIndex(this.externalValue(), function (val) {
                return val[idName] == id;
            });

            this.externalValue()[index] = value;
            this.externalValue.valueHasMutated();
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'externalValue'
                ]);
        },

        onSelectedChange: function () {
            if (!this.externalTransfer) {
                return;
            }

            this.updateExternalValue();
        },

        updateExternalValue: function () {
            var result = $.Deferred(),
                provider = this.selections(),
                selections = provider && provider.getSelections(),
                itemsType = selections && selections.excludeMode ? 'excluded' : 'selected',
                index = provider && provider.indexField,
                rows = provider && provider.rows(),
                canUpdateFromSelection;

            if (!provider) {
                return;
            }

            canUpdateFromSelection =
                itemsType === 'selected' &&
                _.intersection(_.pluck(rows, index), selections.selected).length ===
                selections.selected.length;

            if (canUpdateFromSelection) {
                this.updateFromSelectionData(selections, index, rows);
                result.resolve();
            } else {
                this.updateFromServerData(selections, index, itemsType).done(function () {
                    result.resolve();
                });
            }
            return result;
        },

        updateFromSelectionData: function (selections, index, rows) {
            rows = selections.selected && selections.selected.length ?
                _.filter(rows, function (row) {
                    return _.contains(selections.selected, row[index]);
                }) : [];
            this.set('externalValue', rows);
        },

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

        updateExternalFiltersModifier: function (items) {
            var provider = this.selections(),
                index = provider && provider.indexField,
                filter = {};

            if (!items || !items.length) {
                return;
            }

            filter[provider.indexField] = this.externalFilter;
            filter[provider.indexField].value = _.pluck(items, index);
            this.set('externalFiltersModifier', filter);
        },

        updateValue: function () {
            this.set('value', this.externalValue());
        },

        save: function () {
            this.updateExternalValue().done(this.updateValue);
        }
    });
});
