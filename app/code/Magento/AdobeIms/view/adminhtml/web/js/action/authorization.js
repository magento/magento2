/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Build window params
     * @param {Object} windowParams
     * @returns {String}
     */
    function buildWindowParams(windowParams) {
        var output = '',
            coma = '',
            paramName,
            paramValue;

        for (paramName in windowParams) {
            if (windowParams[paramName]) {
                paramValue = windowParams[paramName];
                output += coma + paramName + '=' + paramValue;
                coma = ',';
            }
        }

        return output;
    }

    return function (config) {
        var authWindow,
            deferred = $.Deferred(),
            watcherId,
            stopWatcherId;

        /**
         * Close authorization window if already opened
         */
        if (window.adobeIMSAuthWindow) {
            window.adobeIMSAuthWindow.close();
        }

        /**
         * Opens authorization window with special parameters
         */
        authWindow = window.adobeIMSAuthWindow = window.open(
            config.url,
            'authorization_widnow',
            buildWindowParams(
                config.popupWindowParams || {
                    width: 500,
                    height: 300
                }
            )
        );

        /**
         * Stop handle
         */
        function stopHandle() {
            // Clear timers
            clearTimeout(stopWatcherId);
            clearInterval(watcherId);

            // Close window
            authWindow.close();
        }

        /**
         * Start handle
         */
        function startHandle() {
            var responseData;

            try {

                if (authWindow.document.domain !== document.domain ||
                    authWindow.document.readyState !== 'complete') {
                    return;
                }

                /**
                 * If within 10 seconds the result is not received, then reject the request
                 */
                stopWatcherId = setTimeout(function () {
                    stopHandle();
                    deferred.reject(new Error('Time\'s up.'));
                }, config.popupWindowTimeout || 60000);

                responseData = authWindow.document.body.innerHTML.match(
                    config.callbackParsingParams.regexpPattern
                );

                if (!responseData) {
                    return;
                }

                stopHandle();

                if (responseData[config.callbackParsingParams.codeIndex] ===
                    config.callbackParsingParams.successCode) {
                    deferred.resolve({
                        isAuthorized: true,
                        lastAuthSuccessMessage: responseData[config.callbackParsingParams.messageIndex]
                    });
                } else {
                    deferred.reject(responseData[config.callbackParsingParams.messageIndex]);
                }
            } catch (e) {
                if (authWindow.closed) {
                    clearTimeout(stopWatcherId);
                    clearInterval(watcherId);

                    // eslint-disable-next-line max-depth
                    if (window.adobeIMSAuthWindow && window.adobeIMSAuthWindow.closed) {
                        deferred.reject(new Error('Authentication window was closed.'));
                    }
                }
            }
        }

        /**
         * Watch a result 1 time per second
         */
        watcherId = setInterval(startHandle, 1000);

        return deferred.promise();
    };
});
