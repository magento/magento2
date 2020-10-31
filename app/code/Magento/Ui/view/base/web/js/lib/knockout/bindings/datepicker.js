/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates datepicker binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'underscore',
    'jquery',
    'mage/translate'
], function (ko, _, $, $t) {
    'use strict';

    var defaults = {
        dateFormat: 'mm\/dd\/yyyy',
        showsTime: false,
        timeFormat: null,
        buttonImage: null,
        buttonImageOnly: null,
        buttonText: $t('Select Date')
    };

    ko.bindingHandlers.datepicker = {
        /**
         * Initializes calendar widget on element and stores it's value to observable property.
         * Datepicker binding takes either observable property or object
         *  { storage: {ko.observable}, options: {Object} }.
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

            require(['mage/calendar'], function () {
                $(el).calendar(options);

                ko.utils.registerEventHandler(el, 'change', function () {
                    observable(this.value);
                });
            });
        },

        /**
         * Update calendar widget on element and stores it's value to observable property.
         * Datepicker binding takes either observable property or object
         *  { storage: {ko.observable}, options: {Object} }.
         * @param {HTMLElement} element - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        update: function (element, valueAccessor) {
            var config = valueAccessor(),
                $element = $(element),
                observable,
                options = {},
                newVal;

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observable = config.storage;
                _.extend(options, config.options);
            } else {
                observable = config;
            }

            require(['moment', 'mage/utils/misc', 'mage/calendar'], function (moment, utils) {
                if (_.isEmpty(observable())) {
                    newVal = null;
                } else {
                    newVal = moment(
                        observable(),
                        utils.convertToMomentFormat(
                            options.dateFormat + (options.showsTime ? ' ' + options.timeFormat : '')
                        )
                    ).toDate();
                }

                $element.datepicker('setDate', newVal);
                $element.blur();
            });
        }
    };
});
