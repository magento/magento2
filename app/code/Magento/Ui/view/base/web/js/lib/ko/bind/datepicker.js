/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates datepicker binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'underscore',
    'jquery',
    'mage/calendar'
], function (ko, _, $) {
    'use strict';

    var defaults = {
        "dateFormat": "mm\/dd\/yyyy",
        "showsTime": false,
        "timeFormat": null,
        "buttonImage": null,
        "buttonImageOnly": null,
        "buttonText": "Select Date"
    }

    ko.bindingHandlers.datepicker = {
        /**
         * Initializes calendar widget on element and stores it's value to observable property.
         * Datepicker binding takes either observable property or object { storage: {ko.observable}, options: {Object} }.
         * For more info about options take a look at "mage/calendar" and jquery.ui.datepicker widget.
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        init: function (el, valueAccessor) {
            var config = valueAccessor(),
                observable,
                options = {};

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observable = config.storage;

                _.extend(options, config.options);
            } else {
                observable = config;
            }

            $(el).calendar(options);

            ko.utils.registerEventHandler(el, 'change', function (e) {
                observable(this.value);
            });
        },

        /**
         * Reads target observable from valueAccessor and writes its' value to el.value
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        update: function(el, valueAccessor){
            var config = valueAccessor(),
                observable,
                value;

            observable = typeof config === 'object' ?
                config.storage :
                config;

            value = observable();

            value ? 
                $(el).datepicker('setDate', value) :
                (el.value = '');
        }
    }
});