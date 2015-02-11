/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/* jshint evil:true */
define('jsbuild', [
    'module'
], function (module) {
    'use strict';

    var requireLoad = require.load,
        build = module.config();

    /**
     * Removes base url of a context from the the provided url string.
     *
     * @param {String} url - Url to be processed.
     * @param {Object} ctx - RequiereJs context object which contains baseUrl property.
     * @returns {String} String without base url.
     */
    function removeBaseUrl(url, ctx) {
        var baseUrl = ctx.config.baseUrl || '',
            index = url.indexOf(baseUrl);

        if (~index) {
            url = url.substring(baseUrl.length - index);
        }

        return url;
    }

    /**
     * Overrides requirejs main loading method to provide
     * support of scripts initialization from a bundle object.
     *
     * @param {Object} context
     * @param {String} moduleName
     * @param {String} url
     */
    require.load = function (context, moduleName, url) {
        var relative = removeBaseUrl(url, context),
            data = build[relative];

        if (data) {
            (new Function(data))();

            context.completeLoad(moduleName);
        } else {
            requireLoad.apply(require, arguments);
        }
    };
});
