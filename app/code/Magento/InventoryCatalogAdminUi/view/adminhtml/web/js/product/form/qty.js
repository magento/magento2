/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_CatalogInventory/js/components/qty-validator-changer'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: false
            }
        },

        /** @inheritdoc */
        getInitialValue: function () {
            var values = [this.source.get(this.dataScope), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        /** @inheritdoc */
        setDifferedFromDefault: function () {
            this._super();

            if (this.value() && parseFloat(this.initialValue) !== parseFloat(this.value())) {
                this.source.set(this.dataScope, this.value());
            } else {
                this.source.remove(this.dataScope);
            }
        }
    });
});
