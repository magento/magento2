/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define('mixins', [
    'module'
], function (module) {
    'use strict';
    var contexts = require.s.contexts,
        defContextName = '_',
        defContext = contexts[defContextName],
        unbundledContextName = '$',
        unbundledContext = contexts[unbundledContextName] = require.s.newContext(unbundledContextName),
        defaultConfig = defContext.config,
        unbundledConfig = {
            baseUrl: defaultConfig.baseUrl,
            paths: defaultConfig.paths,
            shim: defaultConfig.shim,
            config: defaultConfig.config,
            map: defaultConfig.map
        },
        rjsMixins;

    /**
     * Prepare a separate context where modules are not assigned to bundles
     * so we are able to get their true path and corresponding mixins.
     */
    unbundledContext.configure(unbundledConfig);

    /**
     * Checks if specified string contains
     * a plugin spacer '!' substring.
     *
     * @param {String} name - Name, path or alias of a module.
     * @returns {Boolean}
     */
    function hasPlugin(name) {
        return !!~name.indexOf('!');
    }

    /**
     * Adds 'mixins!' prefix to the specified string.
     *
     * @param {String} name - Name, path or alias of a module.
     * @returns {String} Modified name.
     */
    function addPlugin(name) {
        return 'mixins!' + name;
    }

    /**
     * Removes base url from the provided string.
     *
     * @param {String} url - Url to be processed.
     * @param {Object} config - Contexts' configuration object.
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
     * Extracts url (without baseUrl prefix)
     * from a module name ignoring the fact that it may be bundled.
     *
     * @param {String} name - Name, path or alias of a module.
     * @param {Object} config - Context's configuration.
     * @returns {String}
     */
    function getPath(name, config) {
        var url = unbundledContext.require.toUrl(name);

        return removeBaseUrl(url, config);
    }

    /**
     * Checks if specified string represents a relative path (../).
     *
     * @param {String} name - Name, path or alias of a module.
     * @returns {Boolean}
     */
    function isRelative(name) {
        return !!~name.indexOf('./');
    }

    /**
     * Iteratively calls mixins passing to them
     * current value of a 'target' parameter.
     *
     * @param {*} target - Value to be modified.
     * @param {...Function} mixins - List of mixins to apply.
     * @returns {*} Modified 'target' value.
     */
    function applyMixins(target) {
        var mixins = Array.prototype.slice.call(arguments, 1);

        mixins.forEach(function (mixin) {
            target = mixin(target);
        });

        return target;
    }

    rjsMixins = {

        /**
         * Loads specified module along with its' mixins.
         * This method is called for each module defined with "mixins!" prefix
         * in its name that was added by processNames method.
         *
         * @param {String} name - Module to be loaded.
         * @param {Function} req - Local "require" function to use to load other modules.
         * @param {Function} onLoad - A function to call with the value for name.
         * @param {Object} config - RequireJS configuration object.
         */
        load: function (name, req, onLoad, config) {
            var path     = getPath(name, config),
                mixins   = this.getMixins(path),
                deps     = [name].concat(mixins);

            req(deps, function () {
                onLoad(applyMixins.apply(null, arguments));
            });
        },

        /**
         * Retrieves list of mixins associated with a specified module.
         *
         * @param {String} path - Path to the module (without base URL).
         * @returns {Array} An array of paths to mixins.
         */
        getMixins: function (path) {
            var config = module.config() || {},
                mixins;

            // Fix for when urlArgs is set.
            if (path.indexOf('?') !== -1) {
                path = path.substring(0, path.indexOf('?'));
            }
            mixins = config[path] || {};

            return Object.keys(mixins).filter(function (mixin) {
                return mixins[mixin] !== false;
            });
        },

        /**
         * Checks if specified module has associated with it mixins.
         *
         * @param {String} path - Path to the module (without base URL).
         * @returns {Boolean}
         */
        hasMixins: function (path) {
            return this.getMixins(path).length;
        },

        /**
         * Modifies provided names prepending to them
         * the 'mixins!' plugin prefix if it's necessary.
         *
         * @param {(Array|String)} names - Module names, paths or aliases.
         * @param {Object} context - Current RequireJS context.
         * @returns {Array|String}
         */
        processNames: function (names, context) {
            var config = context.config;

            /**
             * Prepends 'mixin' plugin to a single name.
             *
             * @param {String} name
             * @returns {String}
             */
            function processName(name) {
                var path = getPath(name, config);

                if (!hasPlugin(name) && (isRelative(name) || rjsMixins.hasMixins(path))) {
                    return addPlugin(name);
                }

                return name;
            }

            return typeof names !== 'string' ?
                names.map(processName) :
                processName(names);
        }
    };

    return rjsMixins;
});

require([
    'mixins'
], function (mixins) {
    'use strict';

    var contexts = require.s.contexts,
        defContextName = '_',
        defContext = contexts[defContextName],
        unbundledContextName = '$',
        unbundledContext = contexts[unbundledContextName],
        originalContextRequire = defContext.require,
        originalContextConfigure = defContext.configure,
        processNames = mixins.processNames;

    /**
     * Wrap default context's require function which gets called every time
     * module is requested using require call. The upside of this approach
     * is that deps parameter is already normalized and guaranteed to be an array.
     */
    defContext.require = function (deps, callback, errback) {
        deps = processNames(deps, defContext);

        return originalContextRequire(deps, callback, errback);
    };

    /**
     * Wrap original context configuration to update unbundled context,
     * that way it is able to respect any changes done after mixins module has initialized.
     */
    defContext.configure = function (cfg) {
        originalContextConfigure(cfg);
        unbundledContext.configure(cfg);
    };

    /**
     * Copy properties of original 'require' method.
     */
    Object.keys(originalContextRequire).forEach(function (key) {
        defContext.require[key] = originalContextRequire[key];
    });

    /**
     * Wrap shift method from context's definitions queue.
     * Items are added to the queue when a new module is defined and taken
     * from it every time require call happens.
     */
    defContext.defQueue.shift = function () {
        var queueItem = Array.prototype.shift.call(this),
            lastDeps = queueItem && queueItem[1];

        if (Array.isArray(lastDeps)) {
            queueItem[1] = processNames(queueItem[1], defContext);
        }

        return queueItem;
    };
});
