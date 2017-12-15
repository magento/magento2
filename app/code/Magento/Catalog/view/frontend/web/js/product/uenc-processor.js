/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
        'use strict';

        /**
         * Check data to JSON.
         *
         * @returns {Boolean}
         */
        function _isJSON(data) {
            try {
                JSON.parse(data);
            } catch (e) {
                return false;
            }

            return true;
        }

        /**
         * Processes data.
         *
         * @param {Object} data
         * @param {String} placeholder
         * @param {String} uenc
         *
         * @returns {String}
         */
        function _stringProcessor(data, placeholder, uenc) {
            if (data && ~data.indexOf(placeholder)) {
                return data.replace(placeholder, uenc);
            }

            return data;
        }

        /**
         * Processes data.
         *
         * @param {Object} data
         * @param {String} placeholder
         * @param {String} uenc
         *
         * @returns {String}
         */
        function _objectProcessor(data, placeholder, uenc) {
            data = JSON.parse(data);

            if (data.hasOwnProperty('action')) {
                data.action = _stringProcessor(data.action, placeholder, uenc);
            }

            if (data.hasOwnProperty('data') && data.data.hasOwnProperty('uenc')) {
                data.data.uenc = uenc;
            }

            return JSON.stringify(data);
        }

        /**
         * Processes data.
         *
         * @param {Object} data
         * @param {String} placeholder
         *
         * @returns {String}
         */
        return function (data, placeholder) {
            var uenc = btoa(window.location.href).replace('+/=', '-_,');

            placeholder = placeholder || encodeURI('%uenc%');

            return _isJSON(data) ?
                _objectProcessor(data, placeholder, uenc) :
                _stringProcessor(data, placeholder, uenc);

        };
    }
);
