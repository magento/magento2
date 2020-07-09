/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'mage/translate',
    'mage/mage',
    'mage/decorate'
], function (Component, customerData, $, $t) {
    'use strict';

    var sidebarInitialized = false;

    /**
     * Initialize sidebar
     */
    function initSidebar() {
        if (sidebarInitialized) {
            return;
        }

        sidebarInitialized = true;
        $('[data-role=compare-products-sidebar]').decorate('list', true);
    }

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.compareProducts = customerData.get('compare-products');

            initSidebar();
        },

        /**
         * Get counter label
         *
         * @returns {String}
         */
        getCounterLabel: function () {
            var counter = this.compareProducts().count;

            if (counter === 1) {
                return counter + ' ' + $t('item');
            }

            return $t('%1 items').replace('%1', counter);
        }
    });
});
