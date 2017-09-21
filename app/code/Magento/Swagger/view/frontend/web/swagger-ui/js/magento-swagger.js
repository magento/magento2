/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global SwaggerClient SwaggerUi initOAuth hljs*/

/**
 * @api
 */
$(function () {
    'use strict';

    var url = $('#input_baseUrl').val();

    // Pre load translate...
    if (window.SwaggerTranslator) {
        window.SwaggerTranslator.translate();
    }

    /** @function addApiKeyAuthorization */
    function addApiKeyAuthorization() {
        var key = encodeURIComponent($('#input_apiKey')[0].value);

        if (key && key.trim() !== '') {
            window.swaggerUi.api.clientAuthorizations.add(
                'apiKeyAuth',
                new SwaggerClient.ApiKeyAuthorization('Authorization',  'Bearer ' + key, 'header')
            );
        }
    }

    /** @function log */
    function log() {
        if ('console' in window) {
            console.log.apply(console, arguments);
        }
    }

    window.swaggerUi = new SwaggerUi({
        url: url,
        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        dom_id: 'swagger-ui-container',
        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],

        /** @function onComplete */
        onComplete: function () {
            if (typeof initOAuth == 'function') {
                initOAuth({
                    clientId: 'your-client-id',
                    clientSecret: 'your-client-secret',
                    realm: 'your-realms',
                    appName: 'your-app-name',
                    scopeSeparator: ','
                });
            }

            if (window.SwaggerTranslator) {
                window.SwaggerTranslator.translate();
            }

            $('pre code').each(function (i, e) {
                hljs.highlightBlock(e);
            });

            addApiKeyAuthorization();
        },

        /** @function onFailure */
        onFailure: function () {
            log('Unable to Load SwaggerUI');
        },
        docExpansion: 'none',
        apisSorter: 'alpha',
        showRequestHeaders: false
    });

    $('#input_apiKey').change(addApiKeyAuthorization);

    window.swaggerUi.load();
});
