/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function () {
    'use strict';

    /**
     * Removes base url from the the provided string.
     *
     * @param {String} url - Url to be processed.
     * @param {Object} config - RequiereJs config object.
     * @returns {String} String without base url.
     */
    function removeBaseUrl(url, config) {
        var baseUrl = config.baseUrl || '',
            index = url.indexOf(baseUrl);

        if (~index) {
            url = url.substring(baseUrl.length - index);
        }

        return url;
    }

    /**
     * Extension of a requirejs text plugin
     * to load files from a build object.
     */
    define('text', [
        'module',
        'requirejs/text'
    ], function (module, text) {
        var textLoad = text.load,
            build = module.config() || {};

        /**
         * Overrides load method of a 'text' plugin to provide support
         * of loading files from a build object.
         *
         * @param {String} name
         * @param {Function} req
         * @param {Function} onLoad
         * @param {Object} config
         */
        text.load = function (name, req, onLoad, config) {
            var url      = req.toUrl(name),
                relative = removeBaseUrl(url, config),
                data     = build[relative];

            data ?
                onLoad(data) :
                textLoad.apply(text, arguments);
        };

        return text;
    });

    /**
     * Extension of a requirejs 'load' method
     * to load files from a build object.
     */
    define('jsbuild', [
        'module'
    ], function (module) {
        var requireLoad = require.load,
            build = module.config() || {};

        /**
         * Overrides requirejs main loading method to provide
         * support of scripts initialization from a bundle object.
         *
         * @param {Object} context
         * @param {String} moduleName
         * @param {String} url
         */
        require.load = function (context, moduleName, url) {
            var relative = removeBaseUrl(url, context.config),
                data     = build[relative];

            if (data) {
                /* jshint evil:true */
                (new Function(data))();

                context.completeLoad(moduleName);
            } else {
                requireLoad.apply(require, arguments);
            }
        };
    });

})();
