/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define('buildTools', [
], function () {
    'use strict';

    var storage = window.localStorage,
        storeName = 'buildDisabled';

    return {
        isEnabled: storage.getItem(storeName) === null,

        /**
         * Removes base url from the the provided string.
         *
         * @param {String} url - Url to be processed.
         * @param {Object} config - RequiereJs config object.
         * @returns {String} String without base url.
         */
        removeBaseUrl: function (url, config) {
            var baseUrl = config.baseUrl || '',
                index = url.indexOf(baseUrl);

            if (~index) {
                url = url.substring(baseUrl.length - index);
            }

            return url;
        },

        /**
         * Enables build usage.
         */
        on: function () {
            storage.removeItem(storeName);

            location.reload();
        },

        /**
         * Disables build usage.
         */
        off: function () {
            storage.setItem(storeName, 'true');

            location.reload();
        }
    };
});

/**
 * Extension of a requirejs 'load' method
 * to load files from a build object.
 */
define('jsbuild', [
    'module',
    'buildTools'
], function (module, tools) {
    'use strict';

    var build = module.config() || {};

    if (!tools.isEnabled) {
        return;
    }

    require._load = require.load;

    /**
     * Overrides requirejs main loading method to provide
     * support of scripts initialization from a bundle object.
     *
     * @param {Object} context
     * @param {String} moduleName
     * @param {String} url
     */
    require.load = function (context, moduleName, url) {
        var relative = tools.removeBaseUrl(url, context.config),
            data     = build[relative];

        if (data) {
            /* jshint evil:true */
            (new Function(data))();

            context.completeLoad(moduleName);
        } else {
            require._load.apply(require, arguments);
        }
    };
});

/**
 * Extension of a requirejs text plugin
 * to load files from a build object.
 */
define('text', [
    'module',
    'buildTools',
    'requirejs/text'
], function (module, tools, text) {
    'use strict';

    var build = module.config() || {};

    if (!tools.isEnabled) {
        return text;
    }

    text._load = text.load;

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
            relative = tools.removeBaseUrl(url, config),
            data     = build[relative];

        data ?
            onLoad(data) :
            text._load.apply(text, arguments);
    };

    return text;
});
