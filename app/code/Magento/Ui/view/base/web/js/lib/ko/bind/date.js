/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'moment',
    'jquery',
    'date-format-normalizer'
], function(ko, moment, $, convert) {
    'use strict';

    ko.bindingHandlers.date = {

        /**
         * Reads passed date and format from valueAccessor, uses convert function to format it.
         * Writes date to el.innerText using jQuery
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        init: function(element, valueAccessor) {
            var config = valueAccessor(),
                format = convert(config.format),
                date   = moment(config.value).format(format);

            $(element).text(date);
        }
    };
});