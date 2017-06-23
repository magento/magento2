/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Backend/js/validate-store'
], function ($, validateStore) {
    'use strict';

    $.widget('mage.saveWithConfirm', validateStore, {

        options: {
            loadedGroupId: $('[name="store[group_id]"]').val(),
            loadedRootCategoryId: $('[name="group[root_category_id]"]').val(),
            loadedDefaultStoreView: $('[name="group[default_store_id]"]').val(),
            loadedDefaultStore: $('[name="website[default_group_id]"]').val()
        },

        /**
         * Check is it need to show confirmation popup
         *
         * @returns {Boolean}
         */
        _needConfirm: function () {

            /* edit store view*/
            var storeViewEdit = $('[name="store[store_id]"]').length,
                groupId = $('[name="store[group_id]"]').val(),
                isNewStoreView = !$('[name="store[store_id]"]').val(),

            /* edit store */
                storeEdit = $('[name="group[group_id]"]').length,
                storeId = $('[name="group[group_id]"]').val(),
                rootCategoryId = $('[name="group[root_category_id]"]').val(),
                defaultStoreView = $('[name="group[default_store_id]"]').val(),

            /* edit website */
                websiteEdit = $('[name="website[website_id]"]').length,
                defaultStore = $('[name="website[default_group_id]"]').val(),

            /* conditions */
                storeViewUpdated = storeViewEdit && isNewStoreView || this.loadedGroupId !== groupId,
                storeUpdated = storeEdit && storeId &&
                    (this.loadedRootCategoryId !== rootCategoryId || this.loadedDefaultStoreView !== defaultStoreView),
                websiteUpdted = websiteEdit && this.loadedDefaultStore !== defaultStore;

            return storeViewUpdated || storeUpdated || websiteUpdted;
        }
    });

    return $.mage.saveWithConfirm;
});
