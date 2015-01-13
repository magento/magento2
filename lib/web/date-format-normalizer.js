/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([],function(){
    'use strict';

    var formatCache = {};

    function Formatter() {

        /**
         * Convert from string-Magento-date-format
         * to string-moment-js-date-format. Result
         * stored in internal cache.
         * @param {String} zendFormat
         * @return {String}
         */
        return function(zendFormat) {
            var momentFormat = '';

            if(formatCache[zendFormat]) {
                momentFormat = formatCache[zendFormat];
            } else {
                // List of differences. Got from 'MMM d, y h:mm:ss a' -> "MMM D, YYYY h:mm:ss A"
                momentFormat = String(zendFormat).
                    replace('D','DDD').
                    replace('dd','DD').
                    replace('d','D').
                    replace('EEEE','dddd').
                    replace('EEE','ddd').
                    replace('e','d').
                    replace('y','YYYY').
                    replace('a','A').
                    toString();
                formatCache[zendFormat] = momentFormat;
            }

            return momentFormat;
        }
    }

    return new Formatter;
});