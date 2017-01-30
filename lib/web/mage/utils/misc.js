/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var defaultAttributes,
        map;

    defaultAttributes = {
        method: 'post',
        enctype: 'multipart/form-data'
    };

    map = {
        'D': 'DDD',
        'dd': 'DD',
        'd': 'D',
        'EEEE': 'dddd',
        'EEE': 'ddd',
        'e': 'd',
        'y': 'YYYY',
        'a': 'A'
    };

    function limitWrapper(data) {
        var args = _.toArray(arguments).splice(1);

        if (data.id) {
            window.clearTimeout(data.id);
        }

        data.id = window.setTimeout(function () {
            delete data.id;

            data.fn.apply(data.owner, args);
        }, data.limit);
    }

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

        limit: function (owner, target, limit) {
            var data;

            if (!limit) {
                return;
            }

            data = {
                owner: owner,
                limit: limit,
                fn: owner[target]
            };

            owner[target] = limitWrapper.bind(null, data);
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
         */
        submit: function (options, attrs) {
            var form        = document.createElement('form'),
                data        = this.serialize(options.data),
                attributes  = _.extend({}, defaultAttributes, attrs || {}),
                field;

            if (!attributes.action) {
                attributes.action = options.url;
            }

            data.form_key = window.FORM_KEY;

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
        }
    };
});
