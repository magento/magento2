/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['underscore'], function (_) {
    'use strict';

    return {
        /**
         * @param {Object} config
         */
        build: function (config) {
            var types = _.map(_.flatten(config), function (item) {
                return item.type;
            });

            require(types, function () {});
        }
    };
});
