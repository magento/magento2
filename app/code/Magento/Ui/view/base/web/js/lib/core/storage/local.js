/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiEvents'
], function (_, registry, utils, EventsBus) {
    'use strict';

    var root = 'appData',
        localStorage = window.localStorage,
        hasSupport,
        storage;

    /**
     * Flag which indicates whether localStorage is supported.
     */
    hasSupport = (function () {
        var key = '_storageSupported';

        try {
            localStorage.setItem(key, 'true');

            if (localStorage.getItem(key) === 'true') {
                localStorage.removeItem(key);

                return true;
            }

            return false;
        } catch (e) {
            return false;
        }
    })();

    if (!hasSupport) {
        localStorage = {
            _data: {},

            /**
             * Sets value of the specified item.
             *
             * @param {String} key - Key of the property.
             * @param {*} value - Properties' value.
             */
            setItem: function (key, value) {
                this._data[key] = value + '';
            },

            /**
             * Retrieves specified item.
             *
             * @param {String} key - Key of the property to be retrieved.
             */
            getItem: function (key) {
                return this._data[key];
            },

            /**
             * Removes specified item.
             *
             * @param {String} key - Key of the property to be removed.
             */
            removeItem: function (key) {
                delete this._data[key];
            },

            /**
             * Removes all items.
             */
            clear: function () {
                this._data = {};
            }
        };
    }

    /**
     * Extracts and parses data stored in localStorage by the
     * key specified in 'root' varaible.
     *
     * @returns {Object}
     */
    function getRoot() {
        var data = localStorage.getItem(root),
            result = {};

        if (!_.isNull(data) && typeof data != 'undefined') {
            result = JSON.parse(data);
        }

        return result;
    }

    /**
     * Writes provided data to the localStorage.
     *
     * @param {*} data - Data to be stored.
     */
    function setRoot(data) {
        localStorage.setItem(root, JSON.stringify(data));
    }

    /**
     * Provides methods to work with a localStorage
     * as a single nested structure.
     */
    storage = _.extend({

        /**
         * Retrieves value of the specified property.
         *
         * @param {String} path - Path to the property.
         *
         * @example Retrieveing data.
         *      localStoarge =>
         *          'appData' => '
         *              "one": {"two": "three"}
         *          '
         *      storage.get('one.two')
         *      => "three"
         *
         *      storage.get('one')
         *      => {"two": "three"}
         */
        get: function (path) {
            var data = getRoot();

            return utils.nested(data, path);
        },

        /**
         * Sets specified data to the localStorage.
         *
         * @param {String} path - Path of the property.
         * @param {*} value - Value of the property.
         *
         * @example Setting data.
         *      storage.set('one.two', 'four');
         *      => localStoarge =>
         *          'appData' => '
         *              "one": {"two": "four"}
         *          '
         */
        set: function (path, value) {
            var data = getRoot();

            utils.nested(data, path, value);

            setRoot(data);
        },

        /**
         * Removes specified data from the localStorage.
         *
         * @param {String} path - Path to the property that should be removed.
         *
         * @example Removing data.
         *      storage.remove('one.two', 'four');
         *      => localStoarge =>
         *          'appData' => '
         *              "one": {}
         *          '
         */
        remove: function (path) {
            var data = getRoot();

            utils.nestedRemove(data, path);

            setRoot(data);
        }
    }, EventsBus);

    registry.set('localStorage', storage);

    return storage;
});
