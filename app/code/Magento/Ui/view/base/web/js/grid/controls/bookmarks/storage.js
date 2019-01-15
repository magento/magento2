/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mageUtils',
    'Magento_Ui/js/lib/core/storage/local',
    'uiClass'
], function ($, utils, storage, Class) {
    'use strict';

    /**
     * Removes ns prefix for path.
     *
     * @param {String} ns
     * @param {String} path
     * @returns {String}
     */
    function removeNs(ns, path) {
        return path.replace(ns + '.', '');
    }

    return Class.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                data: {
                    namespace: '${ $.namespace }'
                }
            }
        },

        /**
         * Delegates call to the localStorage adapter.
         */
        get: function () {
            return {};
        },

        /**
         * Sends request to store specified data.
         *
         * @param {String} path - Path by which data should be stored.
         * @param {*} value - Value to be sent.
         */
        set: function (path, value) {
            var property = removeNs(this.namespace, path),
                data = {},
                config;

            utils.nested(data, property, value);

            config = utils.extend({
                url: this.saveUrl,
                data: {
                    data: JSON.stringify(data)
                }
            }, this.ajaxSettings);

            $.ajax(config);
        },

        /**
         * Sends request to remove specified data.
         *
         * @param {String} path - Path to the property to be removed.
         */
        remove: function (path) {
            var property = removeNs(this.namespace, path),
                config;

            config = utils.extend({
                url: this.deleteUrl,
                data: {
                    data: property
                }
            }, this.ajaxSettings);

            $.ajax(config);
        }
    });
});
