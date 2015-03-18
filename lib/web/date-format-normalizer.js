/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var map = {
        'D': 'DDD',
        'dd': 'DD',
        'd': 'D',
        'EEEE': 'dddd',
        'EEE': 'ddd',
        'e': 'd',
        'y': 'YYYY',
        'a': 'A'
    };

    return {
        /**
         * Convert from string-Magento-date-format
         * to string-moment-js-date-format. Result
         * stored in internal cache.
         * @param {String} zendFormat
         * @returns {String}
         */
        zendConverter: function (zendFormat) {
            var result;

            _.each(map, function (moment, zend) {
                result = zendFormat.replace(zend, moment);
            });

            return result;
        }
    };
});
