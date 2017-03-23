/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/paging/paging',
    'underscore'
], function (Paging, _) {
    'use strict';

    return Paging.extend({
        defaults: {
            totalTmpl: 'Magento_Catalog/attributes/grid/paging',
            modules: {
                selectionColumn: '${ $.selectProvider }'
            },
            listens: {
                '${ $.selectProvider }:selected': 'changeLabel'
            },
            label: '',
            selectedAttrs: []
        },

        /**
         * Change label.
         *
         * @param {Array} selected
         */
        changeLabel: function (selected) {
            this.selectedAttrs = [];
            _.each(this.selectionColumn().rows(), function (row) {
                if (selected.indexOf(row['attribute_id']) !== -1) {
                    this.selectedAttrs.push(row['attribute_code']);
                }
            }, this);

            this.label(this.selectedAttrs.join(', '));
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('label');

            return this;
        }
    });
});
