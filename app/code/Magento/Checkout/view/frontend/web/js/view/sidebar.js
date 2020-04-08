/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'Magento_Checkout/js/model/sidebar'
], function (Component, $, sidebarModel) {
    'use strict';

    return Component.extend({
        /**
         * @param {HTMLElement} element
         */
        setModalElement: function (element) {
            sidebarModel.setPopup($(element));
        }
    });
});
