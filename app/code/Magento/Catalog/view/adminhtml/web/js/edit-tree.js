/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */
// jscs:disable jsDoc

require([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'loadingPopup',
    'mage/backend/floating-header'
], function (jQuery, confirm) {
    'use strict';

    /**
     * Delete some category
     * This routine get categoryId explicitly, so even if currently selected tree node is out of sync
     * with this form, we surely delete same category in the tree and at backend.
     *
     * @deprecated
     * @see deleteConfirm
     */
    function categoryDelete(url) {
        confirm({
            content: 'Are you sure you want to delete this category?',
            actions: {
                confirm: function () {
                    location.href = url;
                }
            }
        });
    }

    function displayLoadingMask() {
        jQuery('body').loadingPopup();
    }

    window.categoryDelete = categoryDelete;
    window.displayLoadingMask = displayLoadingMask;
});
