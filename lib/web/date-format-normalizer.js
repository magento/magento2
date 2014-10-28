/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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