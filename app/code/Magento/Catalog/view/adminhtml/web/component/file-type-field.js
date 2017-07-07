/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Checks is relevant value
         *
         * @param {String} value
         * @returns {Boolean}
         */
        isRelevant: function (value) {
            if (value === 'file') {
                this.disabled(false);
                this.visible(true);

                return true;
            }

            this.reset();
            this.disabled(true);
            this.visible(false);

            return false;
        }
    });
});
