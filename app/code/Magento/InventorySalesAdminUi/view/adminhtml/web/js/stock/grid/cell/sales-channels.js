/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventorySalesAdminUi/stock/grid/cell/sales-channel-cell.html'
        },

        /**
         * Get sales channels grouped by type
         *
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSalesChannelsGroupedByType: function (record) {
            var result = [];

            _.each(record[this.index], function (channels, type) {
                result.push({
                    type: type,
                    channels: channels
                });
            });

            return result;
        }
    });
});
