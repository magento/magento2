/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

module.exports = {
    defaultConfig: {
        'themes': 'dev/tools/grunt/configs/themes'
    },

    /**
     * Immediately invoked function.
     * Loads user config file.
     */
    userConfig: (function () {
        try {
            return require(process.cwd() + '/grunt-config');
        } catch (error) {
            return null;
        }
    })(),

    /**
     * Loads "themes" file.
     * Load priority:
     *      From user config;
     *      From default config with ".loc" suffix ;
     *      From default config;
     *
     * @returns themes file or error
     */
    getThemes: function () {
        if (this.userConfig && this.userConfig.themes) {
            return require(this.getFullPath(this.userConfig.themes));
        } else {
            try {
                return require(this.getFullPath(this.defaultConfig.themes + '.loc'));
            } catch (error) {
                try {
                    return require(this.getFullPath(this.defaultConfig.themes));
                } catch (error) {
                    throw  error;
                }
            }
        }
    },

    /**
     * Generates full path to file.
     *
     * @param {String} path - relative path to file.
     *
     * @returns {String} Full path to file
     */
    getFullPath: function (path) {
        return process.cwd() + '/' + path;
    }
};
