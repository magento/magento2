/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/provider'
], function (_, Provider) {
    'use strict';

    return Provider.extend({

        /**
         * Reload grid
         * @returns {exports}
         */
        reload: function () {
            if (this.hasFilters()) {
                this._super();

                return this;
            }

            this.trigger('reload');

            this.onReload({
                items: [],
                totalRecords: 0
            });

            return this;
        },

        /**
         * Has filters checker
         * @returns {Boolean}
         */
        hasFilters: function () {
            var params = this.params,
                filters = params.filters || {};

            return _.keys(filters).length > 1;
        }
    });
});
