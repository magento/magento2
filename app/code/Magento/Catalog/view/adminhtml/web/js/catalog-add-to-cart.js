/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/**
 * Stub for admin area - the real add-to-cart functionality is frontend-only
 */
define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.catalogAddToCart', {
        /** @inheritdoc */
        _create: function () {
            // No-op in admin context
        },

        /**
         * Stub method
         */
        submitForm: function () {
            // No-op in admin context
        }
    });

    return $.mage.catalogAddToCart;
});
