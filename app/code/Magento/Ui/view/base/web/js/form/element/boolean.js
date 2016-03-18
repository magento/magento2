/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Converts provided value to boolean.
         *
         * @returns {Boolean}
         */
        normalizeData: function () {
            return !!+this._super();
        },

        /**
         * Calls 'onUpdate' method of parent, if value is defined and instance's
         *     'unique' property set to true, calls 'setUnique' method
         *
         * @return {Object} - reference to instance
         */
        onUpdate: function () {
            if (this.hasUnique) {
                this.setUnique();
            }

            return this._super();
        }
    });
});
