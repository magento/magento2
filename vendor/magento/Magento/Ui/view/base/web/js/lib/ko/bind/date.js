/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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