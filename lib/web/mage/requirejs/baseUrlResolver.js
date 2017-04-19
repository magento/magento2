/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sample configuration:
 *
 require.config({
        "config": {
            "baseUrlInterceptor": {
                "Magento_Ui/js/lib/knockout/bindings/collapsible.js": "../../../../frontend/Magento/luma/en_US/"
            }
        }
    });
 */

/* global jsSuffixRegExp */
/* eslint-disable max-depth */
define('baseUrlInterceptor', [
    'module'
], function (module) {
    'use strict';

    /**
     * RequireJS Context object
     */
    var ctx = require.s.contexts._,

        /**
         * Original function
         *
         * @type {Function}
         */
        origNameToUrl = ctx.nameToUrl,

        /**
         * Original function
         *
         * @type {Function}
         */
        newContextConstr = require.s.newContext;

    /**
     * Remove dots from URL
     *
     * @param {Array} ary
     */
    function trimDots(ary) {
        var i, part, length = ary.length;

        for (i = 0; i < length; i++) {
            part = ary[i];

            if (part === '.') {
                ary.splice(i, 1);
                i -= 1;
            } else if (part === '..') {
                if (i === 1 && (ary[2] === '..' || ary[0] === '..')) {
                    //End of the line. Keep at least one non-dot
                    //path segment at the front so it can be mapped
                    //correctly to disk. Otherwise, there is likely
                    //no path mapping for a path starting with '..'.
                    //This can still fail, but catches the most reasonable
                    //uses of ..
                    break;
                } else if (i > 0) {
                    ary.splice(i - 1, 2);
                    i -= 2;
                }
            }
        }
    }

    /**
     * Normalize URL string (remove '/../')
     *
     * @param {String} name
     * @param {String} baseName
     * @param {Object} applyMap
     * @param {Object} localContext
     * @returns {*}
     */
    function normalize(name, baseName, applyMap, localContext) {
        var lastIndex,
            baseParts = baseName && baseName.split('/'),
            normalizedBaseParts = baseParts;

        //Adjust any relative paths.
        if (name && name.charAt(0) === '.') {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                //Convert baseName to array, and lop off the last part,
                //so that . matches that 'directory' and not name of the baseName's
                //module. For instance, baseName of 'one/two/three', maps to
                //'one/two/three.js', but we want the directory, 'one/two' for
                //this normalization.
                normalizedBaseParts = baseParts.slice(0, baseParts.length - 1);
                name = name.split('/');
                lastIndex = name.length - 1;

                // If wanting node ID compatibility, strip .js from end
                // of IDs. Have to do this here, and not in nameToUrl
                // because node allows either .js or non .js to map
                // to same file.
                if (localContext.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
                    name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
                }

                name = normalizedBaseParts.concat(name);
                trimDots(name);
                name = name.join('/');
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        return name;
    }

    /**
     * Get full url.
     *
     * @param {Object} context
     * @param {String} url
     * @return {String}
     */
    function getUrl(context, url) {
        var baseUrl = context.config.baseUrl,
            newConfig = context.config,
            modulePath = url.replace(baseUrl, ''),
            newBaseUrl,
            rewrite = module.config()[modulePath];

        if (!rewrite) {
            return url;
        }

        newBaseUrl = normalize(rewrite, baseUrl, undefined, newConfig);

        return newBaseUrl + modulePath;
    }

    /**
     * Replace original function.
     *
     * @returns {*}
     */
    ctx.nameToUrl = function () {
        return getUrl(ctx, origNameToUrl.apply(ctx, arguments));
    };

    /**
     * Replace original function.
     *
     * @return {*}
     */
    require.s.newContext = function () {
        var newCtx = newContextConstr.apply(require.s, arguments),
            newOrigNameToUrl = newCtx.nameToUrl;

        /**
         * New implementation of native function.
         *
         * @returns {String}
         */
        newCtx.nameToUrl = function () {
            return getUrl(newCtx, newOrigNameToUrl.apply(newCtx, arguments));
        };

        return newCtx;
    };
});

require(['baseUrlInterceptor'], function () {
    'use strict';

});
