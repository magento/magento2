/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    var sidebarInitialized = false;

    function initSidebar() {
        if (sidebarInitialized) {
            return ;
        }
        sidebarInitialized = true;
        require([
            'jquery',
            'mage/mage'
        ], function ($) {
            $('[data-role=compare-products-sidebar]').mage('compareItems', {
                "removeConfirmMessage": $.mage.__(
                    "Are you sure you would like to remove this item from the compare products?"
                ),
                "removeSelector": "#compare-items a.action.delete",
                "clearAllConfirmMessage": $.mage.__(
                    "Are you sure you would like to remove all products from your comparison?"
                ),
                "clearAllSelector": "#compare-clear-all"
            });
        });
    }

    return Component.extend({
        initialize: function () {
            this._super();
            this.compareProducts = customerData.get('compare-products');

            initSidebar();
        }
    });
});
