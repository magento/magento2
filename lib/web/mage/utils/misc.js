/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'FormData'
], function (_, $) {
    'use strict';

    var defaultAttributes,
        ajaxSettings,
        map;

    defaultAttributes = {
        method: 'post',
        enctype: 'multipart/form-data'
    };

    ajaxSettings = {
        default: {
            method: 'POST',
            cache: false,
            processData: false,
            contentType: false
        },
        simple: {
            method: 'POST',
            dataType: 'json'
        }
    };

    map = {
        'D': 'DDD',
        'dd': 'DD',
        'd': 'D',
        'EEEE': 'dddd',
        'EEE': 'ddd',
        'e': 'd',
        'yyyy': 'YYYY',
        'yy': 'YY',
        'y': 'YYYY',
        'a': 'A'
    };

    return {

        /**
         * Generates a unique identifier.
         *
         * @param {Number} [size=7] - Length of a resulting identifier.
         * @returns {String}
         */
        uniqueid: function (size) {
            var code = Math.random() * 25 + 65 | 0,
                idstr = String.fromCharCode(code);

            size = size || 7;

            while (idstr.length < size) {
                code = Math.floor(Math.random() * 42 + 48);

                if (code < 58 || code > 64) {
                    idstr += String.fromCharCode(code);
                }
            }

            return idstr;
        },

        /**
         * Limits function call.
         *
         * @param {Object} owner
         * @param {String} target
         * @param {Number} limit
         */
        limit: function (owner, target, limit) {
            var fn = owner[target];

            owner[target] = _.debounce(fn.bind(owner), limit);
        },

        /**
         * Converts mage date format to a moment.js format.
         *
         * @param {String} mageFormat
         * @returns {String}
         */
        normalizeDate: function (mageFormat) {
            var result = mageFormat;

            _.each(map, function (moment, mage) {
                result = result.replace(mage, moment);
            });

            return result;
        },

        /**
         * Puts provided value in range of min and max parameters.
         *
         * @param {Number} value - Value to be located.
         * @param {Number} min - Min value.
         * @param {Number} max - Max value.
         * @returns {Number}
         */
        inRange: function (value, min, max) {
            return Math.min(Math.max(min, value), max);
        },

        /**
         * Serializes and sends data via POST request.
         *
         * @param {Object} options - Options object that consists of
         *      a 'url' and 'data' properties.
         * @param {Object} attrs - Attributes that will be added to virtual form.
         */
        submit: function (options, attrs) {
            var form        = document.createElement('form'),
                data        = this.serialize(options.data),
                attributes  = _.extend({}, defaultAttributes, attrs || {}),
                field;

            if (!attributes.action) {
                attributes.action = options.url;
            }

            data['form_key'] = window.FORM_KEY;

            _.each(attributes, function (value, name) {
                form.setAttribute(name, value);
            });

            _.each(data, function (value, name) {
                field = document.createElement('input');

                field.setAttribute('name', name);
                field.setAttribute('type', 'hidden');

                field.value = value;

                form.appendChild(field);
            });

            document.body.appendChild(form);

            form.submit();
        },

        /**
         * Serializes and sends data via AJAX POST request.
         *
         * @param {Object} options - Options object that consists of
         *      a 'url' and 'data' properties.
         * @param {Object} config
         */
        ajaxSubmit: function (options, config) {
            var t = new Date().getTime(),
                settings;

            options.data['form_key'] = window.FORM_KEY;
            options.data = this.prepareFormData(options.data, config.ajaxSaveType);
            settings = _.extend({}, ajaxSettings[config.ajaxSaveType], options || {});

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (data) {
                    data.t = t;
                    config.response.data(data);
                    config.response.status(undefined);
                    config.response.status(!data.error);
                })
                .fail(function () {
                    config.response.status(undefined);
                    config.response.status(false);
                    config.response.data({
                        error: true,
                        messages: 'Something went wrong.',
                        t: t
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        },

        /**
         * Creates FormData object and append this data.
         *
         * @param {Object} data
         * @param {String} type
         * @returns {FormData}
         */
        prepareFormData: function (data, type) {
            var formData;

            if (type === 'default') {
                formData = new FormData();
                _.each(this.serialize(data), function (val, name) {
                    formData.append(name, val);
                });
            } else if (type === 'simple') {
                formData = this.serialize(data);
            }

            return formData;
        }
    };
});
