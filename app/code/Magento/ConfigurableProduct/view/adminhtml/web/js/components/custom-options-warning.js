/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
