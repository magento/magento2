/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiClass',
    'jquery',
    'underscore',
    'uiRegistry'
], function (Class, $, _, registry) {
    'use strict';

    return Class.extend({

        /**
         * Initialize actions and adapter.
         *
         * @param {Object} config
         * @param {Element} elem
         * @returns {Object}
         */
        initialize: function (config, elem) {
            return this._super()
                .initActions()
                .initAdapter(elem);
        },

        /**
         * Creates callback from declared actions.
         *
         * @returns {Object}
         */
        initActions: function () {
            var callbacks = [];

            _.each(this.actions, function (action) {
                callbacks.push({
                    action: registry.async(action.targetName),
                    args: _.union([action.actionName], action.params)
                });
            });

            /**
             * Callback function.
             */
            this.callback = function () {
                _.each(callbacks, function (callback) {
                    callback.action.apply(callback.action, callback.args);
                });
            };

            return this;
        },

        /**
         * Attach callback handler on button.
         *
         * @param {Element} elem
         */
        initAdapter: function (elem) {
            $(elem).on('click', this.callback);

            return this;
        }
    });
});
