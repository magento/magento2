/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    './abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            checked: false,
            links: {
                checked: 'value'
            }
        },

        /**
         * @returns {*|void|Element}
         */
        initObservable: function () {
            return this._super()
                    .observe('checked');
        },

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
