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
            contentSelector: '.${$.name}',
            params: {
                namespace: '${ $.ns }'
            },
            renderSettings: {
                url: '${ $.render_url }',
                dataType: 'html'
            },
            externalLinks: {
                imports: {
                    updateUrl: '${ $.externalProvider }:update_url'
                }
            },
            modules: {
                selections: '${ $.selectionsProvider }'
            }
        },

        initialize: function () {
            _.bindAll(this, 'onRender');
            this._super()
                .render();

            return this;
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'content',
                    'value',
                    'externalValue'
                ]);
        },

        initConfig: function () {
            var self = this._super();

            this.contentSelector = this.contentSelector.replace(/\./g, '_').substr(1);
            $.async('.' + this.contentSelector, function (el) {
                self.contentEl = $(el);
            });

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

            return $.ajax(ajaxSettings);
        },

        onRender: function (data) {
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

        updateExternalValue: function () {
            //add other options
            this.updateFromServerData();
        },

        updateFromServerData: function () {
            var provider = this.selections(),
                selections = provider && provider.getSelections(),
                itemsType = selections && selections.excludeMode ? 'excluded' : 'selected',
                filterType = selections && selections.excludeMode ? 'nin' : 'in',
                selectionsData = {},
                request;

            if (!provider) {
                return;
            }

            //add also filters and search!
            selectionsData['filters_modifier'] = {};
            selectionsData['filters_modifier'][provider.indexField] = {
                    'condition_type': filterType,
                    value: selections[itemsType]
                };

            _.extend(selectionsData, this.params || {});

            request = this.requestData(selectionsData);
            request
                .done(function (data) {
                    this.set('externalValue', data);
                    console.log(this.externalValue());
                }.bind(this))
                .fail(this.onError);
        }
    });
});
