/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            links: {
                linkedValue: false,
                value: null
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super()
                .setDifferedFromDefault();

            return this;
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

            if (parseFloat(this.initialValue) !== parseFloat(this.value())) {
                this.source.set(this.dataScope, this.value());
            } else {
                this.source.remove(this.dataScope);
            }
        }
    });
});
