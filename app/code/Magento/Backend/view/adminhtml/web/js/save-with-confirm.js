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

        /**
         * Check is it need to show confirmation popup
         *
         * @returns {Boolean}
         */
        _needConfirm: function () {

            var storeData = this.settings.storeData,
                
            /* edit store view*/
                storeViewEdit = $('[name="store[store_id]"]').length,
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
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                storeViewUpdated = storeViewEdit && (isNewStoreView || storeData.group_id !== groupId),
                storeUpdated = storeEdit && storeId &&
                    (storeData.root_category_id !== rootCategoryId ||
                    storeData.default_store_id !== defaultStoreView),
                websiteUpdated = websiteEdit && storeData.default_group_id !== defaultStore;
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            return storeViewUpdated || storeUpdated || websiteUpdated;
        }
    });

    return $.mage.saveWithConfirm;
});
