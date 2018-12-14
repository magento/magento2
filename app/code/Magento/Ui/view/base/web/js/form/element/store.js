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

        /**
         * Store component constructor.
         *
         * @returns {exports}
         */
        initialize: function () {
            this._super()
                .filterInitialStores();

            return this;
        },

        /**
         * Filter stores shown based on website selected initially
         *
         * @returns void
         */
        filterInitialStores: function () {
            var websiteId = registry.get(this.parentName + '.website_id');

            if (websiteId) {
                this.filter(websiteId.value(), 'group');
            }
        },

        /**
         * @param {String} data
         */
        websiteIdChanged: function (data) {
            this.filter(data, 'group');
        }

    });
});
