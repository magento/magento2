/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/provider'
], function (_, Provider) {
    'use strict';

    return Provider.extend({
        reload: function () {
            if (this.hasFilters()){
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

        hasFilters: function () {
            var params = this.params,
                filters = params.filters || {};

            return _.keys(filters).length > 1;
        }
    });
});
