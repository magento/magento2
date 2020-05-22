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
            linkedValue: '',
            disableParent: false,
            listens: {
                disabled: 'processState',
                checked: 'processState onCheckedChanged'
            },
            imports: {
                readOnly: 'ns = ${ $.ns }, index = stock_data:disabled'
            }
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
                ._super()
                .observe(['valueFromConfig', 'linkedValue', 'disableParent']);
        },

        /**
         * Handle checked and disabled changes to calculate disableParent value
         */
        processState: function () {
            this.disableParent(this.checked() || this.readOnly);

            if (this.readOnly) {
                this.disable();
            }
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            if (newChecked) {
                this.linkedValue(this.valueFromConfig());
            }

            this._super(newChecked);
        }
    });
});
