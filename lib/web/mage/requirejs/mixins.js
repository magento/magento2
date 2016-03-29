/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define('mixins', [
    'module'
], function (module) {
    'use strict';

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

    return {

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
                mixins = config[path] || {},
                result = [],
                mixin;

            for (mixin in mixins) {
                if (mixins.hasOwnProperty(mixin) && mixins[mixin] !== false) {
                    result.push(mixin);
                }
            }

            return result;
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
         * Modifies items of a 'names' array adding to them
         * a 'mixins!' plugin prefix if it's necessary.
         *
         * @param {Array} names - An array of names, paths or aliases.
         * @param {Object} context - Current requirejs context.
         */
        processNames: function (names, context) {
            var config = context.config,
                index = names.length,
                path,
                name;

            while (index--) {
                name = names[index];
                path = getPath(name, config);

                if (!hasPlugin(name) && (isRelative(name) || this.hasMixins(path))) {
                    names[index] = addPlugin(name);
                }
            }
        }
    };
});

require([
    'mixins'
], function (mixins) {
    'use strict';

    var origRequire      = window.require,
        originalDefine   = window.define,
        commentRegExp    = /(\/\*([\s\S]*?)\*\/|([^:]|^)\/\/(.*)$)/mg,
        cjsRequireRegExp = /[^.]\s*require\s*\(\s*["']([^'"\s]+)["']\s*\)/g,
        contexts         = origRequire.s.contexts,
        defContextName   = '_',
        hasOwn           = Object.prototype.hasOwnProperty,
        key;

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
        var context,
            config,
            contextName = defContextName;

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

        mixins.processNames(deps, context);

        return context.require(deps, callback, errback);
    };

    /**
     * Overrides global 'define' method adding to it dependencies modfication.
     */
    window.define = function (name, deps, callback) {
        var context = getOwn(contexts, defContextName),
            args;

        if (typeof name !== 'string') {
            callback = deps;
            deps = name;
            name = null;
        }

        if (!Array.isArray(deps)) {
            callback = deps;
            deps = null;
        }

        if (!deps && typeof callback === 'function') {
            deps = [];

            if (callback.length) {
                callback
                    .toString()
                    .replace(commentRegExp, '')
                    .replace(cjsRequireRegExp, function (match, dep) {
                        deps.push(dep);
                    });

                deps = (callback.length === 1 ? ['require'] : ['require', 'exports', 'module']).concat(deps);
            }
        }

        if (Array.isArray(deps)) {
            mixins.processNames(deps, context);
        }

        args = [name, deps, callback];

        if (!name) {
            args.shift(0);
        }

        originalDefine.apply(null, args);
    };

    /**
     * Copy properties of original 'require' method.
     */
    for (key in origRequire) {
        if (origRequire.hasOwnProperty(key)) {
            require[key] = origRequire[key];
        }
    }

    /**
     * Copy properties of original 'define' method.
     */
    for (key in originalDefine) {
        if (originalDefine.hasOwnProperty(key)) {
            define[key] = originalDefine[key];
        }
    }

    window.requirejs = window.require;
});
