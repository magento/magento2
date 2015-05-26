/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './sortable'
], function (Sortable) {
    'use strict';

    return Sortable.extend({
        getLabel: function (data) {
            var options = this.options || [],
                label = '';
            data = data || '';

            options.some(function (item) {
                label = item.label;

                return item.value == data;
            });

            return label;
        }
    });
});
