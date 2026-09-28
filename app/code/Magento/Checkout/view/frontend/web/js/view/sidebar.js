/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/sidebar'
], function (Component, ko, $, sidebarModel) {
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
