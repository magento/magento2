/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'mage/mage',
    'mage/decorate'
], function (Component, customerData, $) {
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
        }
    });
});
