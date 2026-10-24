/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/components/html'
], function (Html) {
    'use strict';

    return Html.extend({
        defaults: {
            isConfigurable: false
        },

        /**
         * Updates component visibility state.
         *
         * @param {Boolean} variationsEmpty
         * @returns {Boolean}
         */
        updateVisibility: function (variationsEmpty) {
            var isVisible = this.isConfigurable || !variationsEmpty;

            this.visible(isVisible);

            return isVisible;
        }
    });
});
