/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'Magento_Ui/js/lib/storage',
    'Magento_Ui/js/lib/class'
], function ($, utils, storage, Class) {
    'use strict';

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
                config;

            config = {
                data: {
                    data: {}
                }
            };

            utils.nested(config.data.data, property, value);

            config = utils.extend({
                url: this.saveUrl
            }, this.ajaxSettings, config);

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

            config = {
                data: {
                    data: property
                }
            };

            config = utils.extend({
                url: this.deleteUrl
            }, this.ajaxSettings, config);

            $.ajax(config);
        }
    });
});
