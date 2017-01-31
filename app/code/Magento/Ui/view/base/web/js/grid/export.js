/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiElement'
], function ($, _, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/exportButton',
            selectProvider: 'ns = ${ $.ns }, index = ids',
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
                data = selections ? selections.getSelections() : null,
                itemsType,
                result = {};

            if (data) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                result.filters = data.params.filters;
                result.search = data.params.search;
                result.namespace = data.params.namespace;
                result[itemsType] = data[itemsType];

                if (!result[itemsType].length) {
                    result[itemsType] = false;
                }
            }

            return result;
        },

        getActiveOption: function () {
            return _.findWhere(this.options, {
                value: this.checked()
            });
        },

        buildOptionUrl: function (option) {
            var params = this.getParams();

            if (!params) {
                return 'javascript:void(0);';
            }

            return option.url + '?' + $.param(params);
            //TODO: MAGETWO-40250
        },

        applyOption: function () {
            var option = this.getActiveOption(),
                url = this.buildOptionUrl(option);

            location.href = url;

        }
    });
});
