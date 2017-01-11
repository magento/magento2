/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * Module responsible for collecting statistics
 * data regarding modules that have been loader via bundle.
 */
define('statistician', [
], function () {
    'use strict';

    var storage     = window.localStorage,
        stringify   = JSON.stringify.bind(JSON);

    /**
     * Removes duplicated entries of array, returning new one.
     *
     * @param {Array} arr
     * @returns {Array}
     */
    function uniq(arr) {
        return arr.filter(function (entry, i) {
            return arr.indexOf(entry) >= i;
        });
    }

    /**
     * Takes first array passed, removes all
     * entries which further arrays contain.
     *
     * @returns {Array} Modified array
     */
    function difference() {
        var args    = Array.prototype.slice.call(arguments),
            target  = args.splice(0, 1)[0];

        return target.filter(function (entry) {
            return !args.some(function (arr) {
                return !!~arr.indexOf(entry);
            });
        });
    }

    /**
     * Stringifies 'data' parameter and sets it under 'key' namespace to localStorage.
     *
     * @param {*} data
     * @param {String} key
     */
    function set(data, key) {
        storage.setItem(key, stringify(data));
    }

    /**
     * Gets item from localStorage by 'key' parameter, JSON.parse's it if defined.
     * Else, returns empty array.
     *
     * @param   {String} key
     * @returns {Array}
     */
    function getModules(key) {
        var plain = storage.getItem(key);

        return plain ? JSON.parse(plain) : [];
    }

    /**
     * Concats 'modules' array with one that was previously stored by 'key' parameter
     * in localStorage, removes duplicated entries from resulting array and writes
     * it to 'key' namespace of localStorage via 'set' function.
     *
     * @param {Array} modules
     * @param {String} key
     */
    function storeModules(modules, key) {
        var old = getModules(key);

        set(uniq(old.concat(modules)), key);
    }

    /**
     * Creates Blob, writes passed data to it, then creates ObjectURL string
     * with blob data. In parallel, creates 'a' element, writes resulting ObjectURL
     * to it's href property and fileName parameter as it's download prop.
     * Clicks on 'a' and cleans up file data.
     *
     * @param   {String} fileName
     * @param   {Object} data
     */
    function upload(fileName, data) {
        var a = document.createElement('a'),
            blob,
            url;

        a.style = 'display: none';
        document.body.appendChild(a);

        blob = new Blob([JSON.stringify(data)], {
            type: 'octet/stream'
        });

        url = window.URL.createObjectURL(blob);

        a.href = url;
        a.download = fileName;
        a.click();

        window.URL.revokeObjectURL(url);
    }

    return {

        /**
         * Stores keys of 'modules' object to localStorage under 'all' namespace.
         *
         * @param {Object} modules
         */
        collect: function (modules) {
            storeModules(Object.keys(modules), 'all');
        },

        /**
         * Wraps 'module' in empty array and stores it to localStorage by 'used' namespace.
         *
         * @param {String} module
         */
        utilize: function (module) {
            storeModules([module], 'used');
        },

        /**
         * Returns modules, stores under 'all' namespace in localStorage via
         * getModules function.
         *
         * @return {Array}
         */
        getAll: function () {
            return getModules('all');
        },

        /**
         * Returns modules, stores under 'used' namespace in localStorage via
         * getModules function.
         *
         * @return {Array}
         */
        getUsed: function () {
            return getModules('used');
        },

        /**
         * Returns difference between arrays stored under 'all' and 'used'.
         *
         * @return {Array}
         */
        getUnused: function () {
            var all     = getModules('all'),
                used    = getModules('used');

            return difference(all, used);
        },

        /**
         * Clears "all" and "used" namespaces of localStorage.
         */
        clear: function () {
            storage.removeItem('all');
            storage.removeItem('used');
        },

        /**
         * Create blob containing stats data and download it
         */
        export: function () {
            upload('Magento Bundle Statistics', {
                used: this.getUsed(),
                unused: this.getUnused(),
                all:  this.getAll()
            });
        }
    };
});

/**
 * Extension of a requirejs 'load' method
 * to load files from a build object.
 */
define('jsbuild', [
    'module',
    'buildTools',
    'statistician'
], function (module, tools, statistician) {
    'use strict';

    var build = module.config() || {};

    if (!tools.isEnabled) {
        return;
    }

    require._load = require.load;

    statistician.collect(build);

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
            statistician.utilize(relative);

            new Function(data)();

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
    'mage/requirejs/text'
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
