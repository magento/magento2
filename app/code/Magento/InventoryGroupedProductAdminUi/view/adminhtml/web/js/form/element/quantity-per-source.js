/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/translate',
    'Magento_Ui/js/form/element/abstract'
], function ($t, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementTmpl: 'Magento_InventoryGroupedProductAdminUi/dynamic-rows/cells/cell-source',
            itemsToDisplay: 3,
            isFullList: true,
            showFullListDescription: $t('Show more...'),
            listens: {
                value: 'updateItems'
            }
        },

        /**
         * Observe elements.
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super()
                .observe(['items', 'isFullList']);

            return this;
        },

        /**
         * Prepare data to use.
         *
         * @param {Object} data
         * @private
         */
        updateItems: function (data) {
            this.isFullList(data.length > this.itemsToDisplay);
            this.isFullList() ? this.items(data.slice(0, this.itemsToDisplay)) : this.items(data);
        }
    });
});
