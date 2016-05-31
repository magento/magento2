/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiClass'
], function ($, _, utils, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            validateBeforeSave: true,
            requestConfig: {
                dataType: 'json',
                type: 'POST'
            }
        },

        /**
         * Initializes client instance.
         *
         * @returns {Client} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'onSuccess', 'onError');

            return this._super();
        },

        /**
         * Sends XMLHttpRequest with a provided configuration.
         *
         * @param {Object} config - Configuration of request.
         * @returns {jQueryPromise}
         */
        send: function (config) {
            var deffer  = $.Deferred();

            config = utils.extend({}, this.requestConfig, config);

            $.ajax(config)
                .done(_.partial(this.onSuccess, deffer))
                .fail(_.partial(this.onError, deffer));

            return deffer.promise();
        },

        /**
         * Proxy save method which might invoke
         * data valiation prior to its' saving.
         *
         * @param {Object} data - Data to be processed.
         * @returns {jQueryPromise}
         */
        save: function (data) {
            var save = this._save.bind(this, data);

            return this.validateBeforeSave ?
                this.validate(data).pipe(save) :
                save();
        },

        /**
         * Sends request to validate provided data.
         *
         * @param {Object} data - Data to be validated.
         * @returns {jQueryPromise}
         */
        validate: function (data) {
            return this.send({
                url: this.validateUrl,
                data: data
            });
        },

        /**
         * Sends request to save provided data.
         *
         * @private
         * @param {Object} data - Data to be validated.
         * @returns {jQueryPromise}
         */
        _save: function (data) {
            return this.send({
                url: this.saveUrl,
                data: data
            });
        },

        /**
         * Creates error object with a provided message.
         *
         * @param {String} msg - Errors' message.
         * @returns {Object}
         */
        createError: function (msg) {
            return {
                type: 'error',
                message: msg
            };
        },

        /**
         * Handles ajax error callback.
         *
         * @param {jQueryPromise} promise - Promise to be rejected.
         * @param {jQueryXHR} xhr - See 'jquery' ajax error callback.
         * @param {String} status - See 'jquery' ajax error callback.
         * @param {(String|Object)} err - See 'jquery' ajax error callback.
         */
        onError: function (promise, xhr, status, err) {
            var msg;

            msg = xhr.status !== 200 ?
                xhr.status + ' (' + xhr.statusText + ')' :
                err;

            promise.reject(this.createError(msg));
        },

        /**
         * Handles ajax success callback.
         *
         * @param {jQueryPromise} promise - Promise to be resoloved.
         * @param {*} data - See 'jquery' ajax success callback.
         */
        onSuccess: function (promise, data) {
            var errors;

            if (data.error) {
                errors = _.map(data.messages, this.createError, this);

                promise.reject(errors);
            } else {
                promise.resolve(data);
            }
        }
    });
});
