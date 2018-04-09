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
            sourceCode: null,
            qtyAvailable: 0
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            this.validation['less-than-equals-to'] = this.qtyAvailable;

            return this;
        },

        /**
         * Toggle disabled state.
         *
         * @param {String} selected
         */
        toggleDisable: function (selected) {
            this.disabled(selected ? selected !== this.sourceCode : !!selected);
        }
    });
});
