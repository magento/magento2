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
            profileUrl: 'adobe_ims/user/profile',
            logoutUrl: 'adobe_ims/user/logout',
            user: {
                isAuthorized: false,
                name: '',
                email: '',
                image: ''
            },
            loginConfig: {
                url: 'https://ims-na1.adobelogin.com/ims/authorize',
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
         * @inheritdoc
         */
        initObservable: function () {
            this._super().observe(['user']);

            return this;
        },

        /**
         * Login to Adobe
         *
         * @return {window.Promise}
         */
        login: function () {
            var deferred = $.Deferred();

            if (this.user().isAuthorized) {
                deferred.resolve();
            }
            login(this.loginConfig)
                .then(function (response) {
                    this.loadUserProfile();
                    deferred.resolve(response);
                }.bind(this))
                .fail(function (error) {
                    deferred.reject(error);
                });

            return deferred.promise();
        },

        /**
         * Retrieve data to authorized user.
         *
         * @return array
         */
        loadUserProfile: function () {
            $.ajax({
                type: 'GET',
                url: this.profileUrl,
                showLoader: true,
                dataType: 'json',
                context: this,

                /**
                 * @param {Object} response
                 * @returns void
                 */
                success: function (response) {
                    this.user({
                        isAuthorized: true,
                        name: response.result.name,
                        email: response.result.email,
                        image: response.result.image
                    });
                },

                /**
                 * @param {Object} response
                 * @returns {String}
                 */
                error: function (response) {
                    return response.message;
                }
            });
        },

        /**
         * Logout from adobe account
         */
        logout: function () {
            $.ajax({
                type: 'POST',
                url: this.logoutUrl,
                data: {
                    'form_key': window.FORM_KEY
                },
                dataType: 'json',
                context: this,
                showLoader: true,
                success: function () {
                    this.user({
                        isAuthorized: false,
                        name: '',
                        email: '',
                        image: ''
                    });
                }.bind(this),

                /**
                 * @param {Object} response
                 * @returns {String}
                 */
                error: function (response) {
                    return response.message;
                }
            });
        }
    });
});
