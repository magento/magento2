/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'mage/translate',
    'mageUtils',
    'underscore',
    'uiLayout',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/bindings',
    'Magento_Ui/js/lib/view/utils/async'
], function (Element, $, $t, utils, _, layout, alert) {
    'use strict';

    return Element.extend({
        defaults: {
            content: '',
            template: 'Magento_TestForm/robin',
            showSpinner: true,
            loading: false,
            contentSelector: '.${$.name}',
            behaviourType: 'simple',
            params: {
                namespace: '${ $.ns }'
            },
            renderSettings: {
                url: '${ $.render_url }',
                dataType: 'html'
            },
            settings: {
                simple: {
                },
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
                    updateUrl: '${ $.externalProvider }:update_url',
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
            links: {
                value: '${ $.provider }:${ $.dataScope}'
            },
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

        initialize: function () {
            var self = this._super();

            _.bindAll(this, 'onRender');

            $.async('.' + this.contentSelector, function (el) {
                self.contentEl = $(el);
                self.render();
            });

            return this;
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'content',
                    'value',
                    'externalValue',
                    'loading'
                ]);
        },

        initConfig: function () {
            this._super();
            utils.extend(this, this.settings[this.behaviourType]);
            this.contentSelector = this.contentSelector.replace(/\./g, '_').substr(1);

            return this;
        },

        render: function () {
            var request = this.requestData(this.params, this.renderSettings);

            request
                .done(this.onRender)
                .fail(this.onError);

            return request;
        },

        requestData: function (params, ajaxSettings) {
            var query = utils.copy(params);

            ajaxSettings = _.extend({
                url: this.updateUrl,
                method: 'GET',
                data: query,
                dataType: 'json'
            }, ajaxSettings);

            this.loading(true);

            return $.ajax(ajaxSettings);
        },

        onRender: function (data) {
            this.loading(false);
            this.set('content', data);
            this.contentEl.children().applyBindings();
            this.contentEl.trigger('contentUpdated');
            this.initExternalLinks(this.externalLinks);
        },

        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            alert({
                content: $t('Something went wrong.')
            });
        },

        initExternalLinks: function (external) {
            this.setListeners(external.listens)
                .setLinks(external.links, 'imports')
                .setLinks(external.links, 'exports');

            _.each({
                exports: external.exports,
                imports: external.imports
            }, this.setLinks, this);

            return this;
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
