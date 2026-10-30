/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
