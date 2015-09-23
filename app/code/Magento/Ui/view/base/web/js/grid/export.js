/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiComponent'
], function ($, _, Component) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'ui/grid/exportButton',
            checked: '',
            modules: {
                selections: '${ $.selectProvider }'
            }
        },

        initialize: function () {
            this._super()
                .initChecked();
        },

        initObservable: function () {
            this._super()
                .observe('checked');

            return this;
        },

        initChecked: function () {
            if (!this.checked()) {
                this.checked(
                    this.options[0].value
                );
            }
            return this;
        },

        getParams: function () {
            var selections = this.selections(),
                data = selections.getSelections(),
                itemsType = data.excludeMode ? 'excluded' : 'selected',
                result = {};

            result['filters'] = data.params.filters;
            result['search'] = data.params.search;
            result['namespace'] = data.params.namespace;
            result[itemsType] = data[itemsType];

            if (!result[itemsType].length) {
                result[itemsType] = false;
            }
            return result;
        },

        getActiveOption: function () {
            return _.findWhere(this.options, {
                value: this.checked()
            });
        },

        buildOptionUrl: function (option) {
            var url = option.url + '?';

            return url + $.param(this.getParams());
        },

        applyOption: function () {
            var option = this.getActiveOption(),
                url = this.buildOptionUrl(option);

            location.href = url;
        }
    });
});
