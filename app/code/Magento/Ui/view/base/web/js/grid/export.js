/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/lib/collapsible'
], function ($, _, ko, Collapsible) {
    'use strict';

    return Collapsible.extend({

        defaults: {
            template: 'ui/grid/exportButton',
            checked: '',
            params: {
                filters: {}
            },
            filtersConfig: {
                provider: '${ $.provider }',
                path: 'params.filters'
            },
            imports: {
                'params.filters': '${ $.filtersConfig.provider }:${ $.filtersConfig.path }'
            }
        },

        initialize: function () {
            this._super()
                .observe('checked')
                .initChecked();
        },

        initChecked: function () {
            if (!this.checked()) {
                this.checked(
                    this.options[0].value
                );
            }
            return this;
        },

        applyOption: function () {
            var option = _.filter(this.options, function (op) {
                return op.value === this.checked();
            }, this)[0];

            location.href = option.url + '?' + $.param({
                'filters': this.params.filters
            });
        }
    });
});
