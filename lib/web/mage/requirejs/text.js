/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/* inspired by http://github.com/requirejs/text */
/*global XMLHttpRequest, XDomainRequest */

define(['module'], function (module) {
    'use strict';

    var xmlRegExp = /^\s*<\?xml(\s)+version=[\'\"](\d)*.(\d)*[\'\"](\s)*\?>/im,
        bodyRegExp = /<body[^>]*>\s*([\s\S]+)\s*<\/body>/im,
        stripReg = /!strip$/i,
        defaultConfig = module.config && module.config() || {};

    /**
     * Strips <?xml ...?> declarations so that external SVG and XML documents can be
     * added to a document without worry.
     * Also, if the string is an HTML document, only the part inside the body tag is returned.
     *
     * @param {String} external
     * @returns {String}
     */
    function stripContent(external) {
        var matches;

        if (!external) {
            return '';
        }

        matches = external.match(bodyRegExp);
        external = matches ?
            matches[1] :
            external.replace(xmlRegExp, '');

        return external;
    }

    /**
     * @returns {XMLHttpRequest|XDomainRequest|null}
     */
    function createRequest() {
        //Would love to dump the ActiveX crap in here. Need IE 6 to die first.
        var xhr = null;

        if (typeof XDomainRequest !== 'undefined') {
            xhr = new XDomainRequest();
        } else if (typeof XMLHttpRequest !== 'undefined') {
            xhr = new XMLHttpRequest();
        }

        return xhr;
    }

    /**
     * XHR requester. Returns value to callback.
     *
     * @param {String} url
     * @param {Function} callback
     * @param {Function} fail
     * @param {Object} headers
     */
    function getContent(url, callback, fail, headers) {
        var xhr = createRequest(),
            header,
            errorHandler = fail || Function();

        xhr.open('GET', url, true);

        //Allow plugins direct access to xhr headers
        for (header in headers) {
            if (headers.hasOwnProperty(header)) {
                xhr.setRequestHeader(header.toLowerCase(), headers[header]);
            }
        }

        //Allow overrides specified in config
        if (defaultConfig.onXhr) {
            defaultConfig.onXhr(xhr, url);
        }

        /**
         * onload handler
         */
        xhr.onreadystatechange = function () {
            var status = xhr.status || 0,
                error;

            if (xhr.readyState !== 4) {
                return;
            }

            if (status > 399 && status < 600) {
                //An http 4xx or 5xx error. Signal an error.
                error = new Error(url + ' HTTP status: ' + status);
                error.xhr = xhr;

                errorHandler(error);

                if (defaultConfig.onXhrFailure) {
                    defaultConfig.onXhrFailure(xhr, url);
                }

                return;
            }

            callback(xhr.responseText);

            if (defaultConfig.onXhrComplete) {
                defaultConfig.onXhrComplete(xhr, url);
            }
        };
        xhr.send(null);
    }

    /**
     * Main method used by RequireJs.
     *
     * @param {String} name - has format: some.module.filext!strip
     * @param {Function} req
     * @param {Function|undefined} onLoad
     */
    function loadContent(name, req, onLoad) {

        var toStrip = stripReg.test(name),
            url = req.toUrl(name.replace(stripReg, '')),
            headers = defaultConfig.headers;

        getContent(url, function (content) {
                content = toStrip ? stripContent(content) : content;
                onLoad(content);
            }, onLoad.error, headers);
    }

    return {
        load: loadContent,
        get: getContent
    };
});
