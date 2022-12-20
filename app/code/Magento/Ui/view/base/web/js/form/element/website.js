/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    './select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            customerId: null,
            isGlobalScope: 0
        },

        /**
         * Website component constructor.
         * @returns {exports}
         */
        initialize: function () {
            this._super();

            return this;
        }
    });
});
