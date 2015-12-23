/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './insert',
    'mageUtils',
    'underscore'
], function (Insert, utils, _) {
    'use strict';

    return Insert.extend({
        defaults: {
            behaviourType: 'simple',
            settings: {
                edit: {
                    externalLinks: {
                        listens: {
                            '${ $.editorProvider }:changed': 'onChangeRecord'
                        }
                    }
                }
            },
            externalLinks: {
                imports: {
                    onSelectedChange: '${ $.selectionsProvider }:selected'
                },
                exports: {
                    externalFiltersModifier: '${ $.externalProvider }:params.filters_modifier'
                }
            },
            modules: {
                selections: '${ $.selectionsProvider }'
            },
            immediateUpdateBySelection: false,
            listens: {
                value: 'updateExternalFiltersModifier',
                externalValue: 'onSetExternalValue'
            },
            externalFiltersModifier: {},
            externalFilter: {
                'condition_type': 'nin',
                value: []
            }
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
            if (!this.immediateUpdateBySelection) {
                return;
            }

            this.updateExternalValue();
        },

        updateExternalValue: function () {
            var provider = this.selections(),
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
            } else {
                this.updateFromServerData(selections, index, itemsType);
            }
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
                    this.set('externalValue', data);
                    this.loading(false);
                }.bind(this))
                .fail(this.onError);
        },

        updateExternalFiltersModifier: function (items) {
            var provider = this.selections(),
                index = provider && provider.indexField;

            if (!items || !items.length) {
                return;
            }

            this.externalFilter.value = _.pluck(items, index);
            this.set('externalFiltersModifier.' + provider.indexField, this.externalFilter);
        },

        onSetExternalValue: function () {
            debugger;
            //if (this.immediateUpdateBySelection) {
            //    this.save();
            //}
            //
            //return this;
        },

        save: function () {
            this.set('value', this.externalValue());
        }
    });
});
