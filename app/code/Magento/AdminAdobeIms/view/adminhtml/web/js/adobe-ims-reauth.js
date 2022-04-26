/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'Magento_AdobeIms/js/action/authorization',
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
         * Login to Adobe
         *
         * @return {window.Promise}
         */
        login: function () {

            var deferred = $.Deferred();
            var loginConfig = this.loginConfig;

            /**
             * Does only work right now, when you remove the data_attribute of the save button
             * src/app/code/Magento/Backend/Block/Widget/Form/Container.php:131
             */
            $("#save").click('click', function(e) {
                e.preventDefault();

                //check if reAuthToken exists and not expired
                //return true;

                //if reAuth token does not exist or is expired
                login(loginConfig)
                    .then(function (response) {
                        deferred.resolve(response);
                    }.bind(this))
                    .fail(function (error) {
                        deferred.reject(error);
                    });

                return false;
            });

            return deferred.promise();
        }
    });
});
