/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'underscore',
    'mage/mage',
    'mage/decorate'
], function (Component, customerData, $, _) {
    'use strict';

    var sidebarInitialized = false,
        compareProductsReloaded = false;

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
            if (!compareProductsReloaded
                && !_.isEmpty(this.compareProducts())
                //Expired section names are reloaded on page load
                && _.indexOf(customerData.getExpiredSectionNames(), 'compare-products') === -1
                && window.checkout
                && window.checkout.storeId
                && window.checkout.storeId !== this.compareProducts().storeId
            ) {
                //set count to 0 to prevent "compared products" blocks and count to show with wrong count and items
                this.compareProducts().count = 0;
                customerData.reload(['compare-products'], false);
                compareProductsReloaded = true;
            }
            initSidebar();
        }
    });
});
