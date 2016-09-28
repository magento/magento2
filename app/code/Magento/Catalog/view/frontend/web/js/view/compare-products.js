/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (Component, customerData) {
    'use strict';

    var sidebarInitialized = false;

    function initSidebar() {
        if (sidebarInitialized) {
            return;
        }
        sidebarInitialized = true;
        require([
            'jquery',
            'mage/mage'
        ], function ($) {
            /*eslint-disable max-len*/
            $('[data-role=compare-products-sidebar]').mage('compareItems', {
                'removeConfirmMessage': $.mage.__('Are you sure you want to remove this item from your Compare Products list?'),
                'removeSelector': '#compare-items a.action.delete',
                'clearAllConfirmMessage': $.mage.__('Are you sure you want to remove all items from your Compare Products list?'),
                'clearAllSelector': '#compare-clear-all'
            });

            /*eslint-enable max-len*/
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
