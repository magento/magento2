/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define('mixins', [
    'module'
], function (module) {
    'use strict';

    var rjsMixins;

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
     * from a modules' name.
     *
     * @param {String} name - Name, path or alias of a module.
     * @param {Object} config - Contexts' configuartion.
     * @returns {String}
     */
    function getPath(name, config) {
        var url = require.toUrl(name);

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
     * Iterativly calls mixins passing to them
     * current value of a 'target' parameter.
     *
     * @param {*} target - Value to be modified.
     * @param {...Function} mixins
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
         *
         * @param {String} name - Module to be loaded.
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
         * @param {String} path - Path to the module (without base url).
         * @returns {Array} An array of paths to mixins.
         */
        getMixins: function (path) {
            var config = module.config() || {},
            mixins;

            // fix for when urlArgs is set
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
         * @param {String} path - Path to the module (without base url).
         * @returns {Boolean}
         */
        hasMixins: function (path) {
            return this.getMixins(path).length;
        },

        /**
         * Modifies provided names perpending to them
         * the 'mixins!' plugin prefix if it's necessary.
         *
         * @param {(Array|String)} names - Module names, paths or aliases.
         * @param {Object} context - Current requirejs context.
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

    var originalRequire  = window.require,
        originalDefine   = window.define,
        contexts         = originalRequire.s.contexts,
        defContextName   = '_',
        hasOwn           = Object.prototype.hasOwnProperty,
        getLastInQueue;

    getLastInQueue =
        '(function () {' +
            'var queue  = globalDefQueue,' +
                'item   = queue[queue.length - 1];' +
            '' +
            'return item;' +
        '})();';

    /**
     * Returns property of an object if
     * it's not defined in it's prototype.
     *
     * @param {Object} obj - Object whose property should be retrieved.
     * @param {String} prop - Name of the property.
     * @returns {*} Value of the property or false.
     */
    function getOwn(obj, prop) {
        return hasOwn.call(obj, prop) && obj[prop];
    }

    /**
     * Overrides global 'require' method adding to it dependencies modfication.
     */
    window.require = function (deps, callback, errback, optional) {
        var contextName = defContextName,
            context,
            config;

        if (!Array.isArray(deps) && typeof deps !== 'string') {
            config = deps;

            if (Array.isArray(callback)) {
                deps = callback;
                callback = errback;
                errback = optional;
            } else {
                deps = [];
            }
        }

        if (config && config.context) {
            contextName = config.context;
        }

        context = getOwn(contexts, contextName);

        if (!context) {
            context = contexts[contextName] = require.s.newContext(contextName);
        }

        if (config) {
            context.configure(config);
        }

        deps = mixins.processNames(deps, context);

        return context.require(deps, callback, errback);
    };

    /**
     * Overrides global 'define' method adding to it dependencies modfication.
     */
    window.define = function (name, deps, callback) { // eslint-disable-line no-unused-vars
        var context     = getOwn(contexts, defContextName),
            result      = originalDefine.apply(this, arguments),
            queueItem   = require.exec(getLastInQueue),
            lastDeps    = queueItem && queueItem[1];

        if (Array.isArray(lastDeps)) {
            queueItem[1] = mixins.processNames(lastDeps, context);
        }

        return result;
    };

    /**
     * Copy properties of original 'require' method.
     */
    Object.keys(originalRequire).forEach(function (key) {
        require[key] = originalRequire[key];
    });

    /**
     * Copy properties of original 'define' method.
     */
    Object.keys(originalDefine).forEach(function (key) {
        define[key] = originalDefine[key];
    });

    window.requirejs = window.require;
});
