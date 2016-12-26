/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
define(['underscore'], function (_) {
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
