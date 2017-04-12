/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var defaultConfig = {},

    /**
     * Generates full path to file.
     *
     * @param {String} path - relative path to file.
     *
     * @returns {String} Full path to file
     */
    getFullPath = function (path) {
        return process.cwd() + '/' + path;
    },

    /**
     * Returns file.
     *
     * @param {String} path - relative path to file.
     *
     * @returns {Object|Null} File or NULL
     */
    getFile = function (path) {
        try {
            return require(getFullPath(path));
        } catch (error) {
            return null;
        }
    },

    /**
     * Immediately invoked function.
     * Loads user config file.
     */
    userConfig = (function () {
        try {
            return require(process.cwd() + '/grunt-config');
        } catch (error) {
            return null;
        }
    })();

module.exports = {

    /**
     * Loads file.
     * Load priority:
     *      From user config;
     *      From default config with ".loc" suffix ;
     *      From default config;
     *
     * @param {String} alias
     *
     * @returns {Object} themes file or error
     */
    get: function (alias) {
        var tmp;

        if (userConfig && userConfig[alias]) {
            return require(getFullPath(userConfig[alias]));
        } else if (tmp = getFile(defaultConfig[alias] + '.loc') ||  getFile(defaultConfig[alias])) {
            return tmp;
        } else {
            throw new Error('Cannot find file. Alias "' + alias + '" not set. ' +
                'Use "filesRouter.set" method to set it.').stack;
        }
    },

    /**
     * Sets file alias.
     *
     * @param {String} alias
     * @param {String} path
     */
    set: function (alias, path) {
        defaultConfig[alias] = path;
    }
};
