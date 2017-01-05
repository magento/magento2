/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, DynamicRows) {
    'use strict';

    return DynamicRows.extend({

        /**
         * Init header elements
         */
        initHeader: function () {
            var labels;

            this._super();
            labels = _.clone(this.labels());
            labels = _.sortBy(labels, function (label) {
                return label.sortOrder;
            });

            this.labels(labels);
        }
    });
});
