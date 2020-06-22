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
        getCounterLabel: function () {
            var counter = this.wishlist().counter;

            if (counter === 1) {
                return counter + ' ' + $t('item');
            } else {
                return $t('%1 items').replace('%1', counter);
            }
        },
    });
});
