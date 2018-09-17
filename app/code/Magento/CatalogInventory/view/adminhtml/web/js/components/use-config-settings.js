/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (checkbox) {
    'use strict';

    return checkbox.extend({
        defaults: {
            valueFromConfig: '',
            linkedValue: ''
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
                ._super()
                .observe(['valueFromConfig', 'linkedValue']);
        },

        /**
         * @inheritdoc
         */
        'onCheckedChanged': function (newChecked) {
            if (newChecked) {
                this.linkedValue(this.valueFromConfig());
            }

            this._super(newChecked);
        }
    });
});
