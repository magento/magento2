/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$(function () {
    var url = $('#input_baseUrl').val();

    // Pre load translate...
    if(window.SwaggerTranslator) {
        window.SwaggerTranslator.translate();
    }
    window.swaggerUi = new SwaggerUi({
        url: url,
        dom_id: "swagger-ui-container",
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
        onComplete: function(swaggerApi, swaggerUi){
            if(typeof initOAuth == "function") {
                initOAuth({
                    clientId: "your-client-id",
                    clientSecret: "your-client-secret",
                    realm: "your-realms",
                    appName: "your-app-name",
                    scopeSeparator: ","
                });
            }

            if(window.SwaggerTranslator) {
                window.SwaggerTranslator.translate();
            }

            $('pre code').each(function(i, e) {
                hljs.highlightBlock(e)
            });

            addApiKeyAuthorization();
        },
        onFailure: function(data) {
            log("Unable to Load SwaggerUI");
        },
        docExpansion: "none",
        apisSorter: "alpha",
        showRequestHeaders: false
    });

    function addApiKeyAuthorization(){
        var key = encodeURIComponent($('#input_apiKey')[0].value);
        if(key && key.trim() != "") {
            var apiKeyAuth = new SwaggerClient.ApiKeyAuthorization("Authorization",  "Bearer " + key, "header");
            window.swaggerUi.api.clientAuthorizations.add("apiKeyAuth", apiKeyAuth);
        }
    }

    $('#input_apiKey').change(addApiKeyAuthorization);


    window.swaggerUi.load();

    function log() {
        if ('console' in window) {
            console.log.apply(console, arguments);
        }
    }
});
