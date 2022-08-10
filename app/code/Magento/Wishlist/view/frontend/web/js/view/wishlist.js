/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (Component, customerData, $t) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.wishlist = customerData.get('wishlist');
        },

        /**
         * Get counter label
         *
         * @returns {String}
         */
        getCounterLabel: function () {
            var counter = this.wishlist().counter;

            if (counter === 1) {
                return counter + ' ' + $t('item');
            }

            return $t('%1 items').replace('%1', counter);
        }
    });
});
