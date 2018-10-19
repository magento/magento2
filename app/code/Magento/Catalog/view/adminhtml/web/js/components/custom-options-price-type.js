/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (_, Select, uiRegistry) {
    'use strict';

    return Select.extend({
        /**
         * {@inheritdoc}
         */
        onUpdate: function () {
            this._super();

            this.updateAddBeforeForPrice();
        },

        /**
         * {@inheritdoc}
         */
        setInitialValue: function () {
            this._super();

            this.updateAddBeforeForPrice();

            return this;
        },

        /**
         * Update addbefore for price field. Change it to currency or % depends of price_type value.
         */
        updateAddBeforeForPrice: function () {
            var addBefore, currentValue, priceIndex, priceName, uiPrice;

            priceIndex = typeof this.imports.priceIndex == 'undefined' ? 'price' : this.imports.priceIndex;
            priceName = this.parentName + '.' + priceIndex;

            uiPrice = uiRegistry.get(priceName);

            if (uiPrice && uiPrice.addbeforePool) {
                currentValue = this.value();

                uiPrice.addbeforePool.forEach(function (item) {
                    if (item.value === currentValue) {
                        addBefore = item.label;
                    }
                });

                if (typeof addBefore != 'undefined') {
                    uiPrice.addBefore(addBefore);
                }
            }
        }
    });
});
