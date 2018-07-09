/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (AbstractField) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            notifyStockQtyUseDefault: '',
            manageStock: '',
            listens: {
                notifyStockQtyUseDefault: 'onChange',
                manageStock: 'onChange'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this
                ._super()
                .observe(['notifyStockQtyUseDefault', 'manageStock']);
        },

        /**
         * Disable input when Manage Stock switched off or Notify Quantity Use Default
         */
        onChange: function () {
            this.disabled(
                this.notifyStockQtyUseDefault() ||
                this.manageStock()
            );
        }
    });
});
