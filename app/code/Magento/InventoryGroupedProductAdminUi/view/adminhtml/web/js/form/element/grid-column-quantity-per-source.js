/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/translate',
    'Magento_Ui/js/grid/columns/column'
], function ($t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventoryGroupedProductAdminUi/grid/column/quantity-per-source',
            itemsToDisplay: 3,
            showFullListDescription: $t('Show more...')
        },

        /**
         * Get source items from product data.
         *
         * @param {Object} rowData
         * @returns {Array}
         */
        getSourceItemsData: function (rowData) {
            return rowData['quantity_per_source'];
        }
    });
});
