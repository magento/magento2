/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_AdobeIms/js/action/authorization'
], function (Component, $, login) {
    'use strict';

    return Component.extend({
        defaults: {
            loginConfig: {
                url: 'https://ims-na1-stg.adobelogin.com/ims/authorize',
                callbackParsingParams: {
                    regexpPattern: /auth\[code=(success|error);message=(.+)\]/,
                    codeIndex: 1,
                    messageIndex: 2,
                    nameIndex: 3,
                    successCode: 'success',
                    errorCode: 'error'
                },
                popupWindowParams: {
                    width: 500,
                    height: 600,
                    top: 100,
                    left: 300
                },
                popupWindowTimeout: 60000
            }
        },

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.login();
        },

        /**
         * Open popup for Adobe reauth
         *
         * @return {window.Promise}
         */
        login: function () {
            var deferred = $.Deferred(),
                loginConfig = this.loginConfig;

            $('input.ims_verification').on('click', function () {
                login(loginConfig)
                    .then(function (response) {
                        if (response.isAuthorized === true) {
                            $('input.ims_verified').val(true);
                        }
                        deferred.resolve(response);
                    })
                    .fail(function (error) {
                        deferred.reject(error);
                    });
            });

            return deferred.promise();
        }
    });
});
