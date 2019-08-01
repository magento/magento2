/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global SwaggerTranslator SwaggerUIBundle SwaggerUIStandalonePreset */

/**
 * @api
 */
(function () {
    'use strict';

    var elementBaseUrl = document.querySelector('#input_baseUrl'),
        url = elementBaseUrl.value,
        ui;

    // Pre load translate...
    if (SwaggerTranslator) {
        SwaggerTranslator.translate();
    }

    /**
     * Takes token from input and adds it to request header.
     */
    function addApiKeyAuthorization(e) {
        var key = encodeURIComponent(e.target.value).trim();

        if (key) {
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

    ui = new SwaggerUIBundle({
        url: url,
        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        dom_id: '#swagger-ui-container',
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        deepLinking: true,
        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
        docExpansion: 'none',
        apisSorter: 'alpha',
        showRequestHeaders: false,
        layout: 'StandaloneLayout'
    });

    document.querySelector('#input_apiKey').addEventListener('change', addApiKeyAuthorization);
    document.querySelector('#explore').addEventListener('click', function () {
        ui.specActions.download();
    });
})();
