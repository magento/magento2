/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global SwaggerUIBundle SwaggerUIStandalonePreset */

/**
 * @api
 */
(function () {
    'use strict';

    var elementBaseUrl = document.querySelector('#swagger-ui'),
        url = elementBaseUrl.dataset.baseUrl,
        originalAuthorize,
        originalLogout;

    /**
     * Adds token to request header.
     */
    function addApiKeyAuthorization(key) {
        if (key && typeof key !== 'undefined') {
            key = key.trim();

            /**
             * Adds Auth token to request header.
             *
             * @param {Object} req
             *
             * @returns {Object} req
             */
            ui.getConfigs().requestInterceptor = function (req) {
                req.headers.Authorization = 'Bearer ' + key;

                return req;
            };
        }
    }

    /**
     * Remove token from request header.
     */
    function removeApiKeyAuthorization() {
        /**
         * Remove Auth token from request header.
         *
         * @param {Object} req
         *
         * @returns {Object} req
         */
        ui.getConfigs().requestInterceptor = function (req) {
            delete req.headers.Authorization;

            return req;
        };
    }

    // Begin Swagger UI call region
    const ui = SwaggerUIBundle({
        url: url,
        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        dom_id: '#swagger-ui',
        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        docExpansion: 'none',
        layout: 'StandaloneLayout'
    });
    // End Swagger UI call region
    window.ui = ui;

    originalAuthorize = ui.authActions.authorize;
    ui.authActions.authorize = function (payload) {
        var apiKeyData = payload.api_key,
            keyValue = typeof apiKeyData !== 'undefined' ? apiKeyData.value : '';

        addApiKeyAuthorization(keyValue);
        ui.specActions.download();

        return originalAuthorize(payload);
    };

    originalLogout = ui.authActions.logout;
    ui.authActions.logout = function (payload) {
        removeApiKeyAuthorization();
        ui.specActions.download();

        return originalLogout(payload);
    };
})();
