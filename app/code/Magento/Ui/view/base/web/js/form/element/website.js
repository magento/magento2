/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

            if (this.customerId || this.isGlobalScope) {
                this.disable(true);
            }

            return this;
        }
    });
});
