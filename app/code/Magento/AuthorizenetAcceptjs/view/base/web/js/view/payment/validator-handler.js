/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_AuthorizenetAcceptjs/js/view/payment/response-validator'
], function ($, responseValidator) {
    'use strict';

    return {
        validators: [],

        /**
         * Init list of validators
         */
        initialize: function () {
            this.add(responseValidator);
        },

        /**
         * Add new validator
         * @param {Object} validator
         */
        add: function (validator) {
            this.validators.push(validator);
        },

        /**
         * Run pull of validators
         * @param {Object} context
         * @param {Function} callback
         */
        validate: function (context, callback) {
            var self = this,
                deferred;

            // no available validators
            if (!self.validators.length) {
                callback(true);

                return;
            }

            // get list of deferred validators
            deferred = $.map(self.validators, function (current) {
                return current.validate(context);
            });

            $.when.apply($, deferred)
                .done(function () {
                    callback(true);
                }).fail(function (error) {
                    callback(false, error);
                });
        }
    };
});
