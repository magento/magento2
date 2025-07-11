/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function () {
    'use strict';
    var combo        = require('./combo'),
        themes       = require('../tools/files-router').get('themes'),
        _            = require('underscore'),
        themeOptions = {},
        lessOptions  = {
            options: {
                sourceMap: true,
                strictImports: false,
                sourceMapRootpath: '/',
                sourceMapBasepath: function () {
                    this.sourceMapURL = this.sourceMapFilename.substr(this.sourceMapFilename.lastIndexOf('/') + 1);
                    return 'pub/';
                },
                dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
                ieCompat: false
            },
            setup: {
                files: {
                    '<%= path.css.setup %>/setup.css': '<%= path.less.setup %>/_setup.less'
                }
            },
            updater: {
                files: {
                    '<%= path.css.updater %>/updater.css': '<%= path.less.setup %>/_setup.less'
                }
            },
            documentation: {
                files: {
                    '<%= path.doc %>/docs.css': '<%= path.doc %>/source/docs.less'
                }
            }
        };

    _.each(themes, function (theme, name) {
        themeOptions[name] = {
            files: combo.lessFiles(name)
        };
    });

    /**
     * Compiles Less to CSS and generates necessary files if requested.
     */
    module.exports = _.extend(themeOptions, lessOptions);
})();
