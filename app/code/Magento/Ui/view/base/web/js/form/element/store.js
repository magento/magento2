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

        initialize: function () {
            this._super();

            this.filterInitialStores();
        },

        filterInitialStores: function () {
            var websiteId = registry.get(this.parentName + '.website_id');

            if(websiteId) {
                this.filter(websiteId.value(), 'group');
            }
        },

        websiteIdChanged: function (data) {
            this.filter(data, 'group');
        }

    });
});
