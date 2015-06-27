/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/lib/events'
], function (_, registry, utils, EventsBus) {
    'use strict';

    var root = 'appData',
        storage;

    function getRoot() {
        var data = localStorage.getItem(root);

        return !_.isNull(data) ? JSON.parse(data) : {};
    }

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
