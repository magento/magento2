/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiClass',
    'Magento_AuthorizenetAcceptjs/js/view/payment/acceptjs-factory',
    'Magento_AuthorizenetAcceptjs/js/view/payment/validator-handler'
], function ($, Class, acceptjsFactory, validatorHandler) {
    'use strict';

    return Class.extend({
        defaults: {
            environment: 'production'
        },

        /**
         * @{inheritDoc}
         */
        initialize: function () {
            validatorHandler.initialize();

            this._super();
        },

        /**
         * Creates the token pair with the provided data
         *
         * @return {jQuery.Deferred}
         */
        createTokens: function (data) {
            var self = this,
                deferred = $.Deferred();

            if (self.acceptjsClient) {
                self._createTokens(deferred, data);
            } else {
                acceptjsFactory(self.environment)
                    .done(function (client) {
                        self.acceptjsClient = client;
                        self._createTokens(deferred, data);
                    });
            }

            return deferred.promise();
        },

        /**
         * Creates a token from the payment information in the form
         *
         * @param {jQuery.Deferred} deferred
         * @param {Object} data
         */
        _createTokens: function (deferred, data) {
            this.acceptjsClient.dispatchData(data, function (response) {
                validatorHandler.validate(response, function (valid, messages) {
                    if (valid) {
                        deferred.resolve({
                            opaqueDataDescriptor: response.opaqueData.dataDescriptor,
                            opaqueDataValue: response.opaqueData.dataValue
                        });
                    } else {
                        deferred.reject(messages);
                    }
                });
            });
        }
    });
});
