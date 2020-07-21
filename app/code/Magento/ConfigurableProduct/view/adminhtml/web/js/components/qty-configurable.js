/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                isConfigurable: '!ns = ${ $.ns }, index = configurable-matrix:isEmpty'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            // resolve initial disable state
            this.handleQtyValue(this.isConfigurable);

            /** important to set this listener in initialize because of a different order of processing.
             * Do not move to defaults->listens section */
            this.setListeners({
                isConfigurable: 'handleQtyValue'
            });

            return this;
        },

        /**
         * Disable and clear Qty if product type changed to configurable
         *
         * @param {String} isConfigurable
         */
        handleQtyValue: function (isConfigurable) {
            this.disabled(!!this.isUseDefault() || isConfigurable);

            if (isConfigurable) {
                this.clear();
            }
        }
    });
});
