/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo  = require('./combo'),
    themes = require('./themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        files: combo.lessFiles(name)
    };
});

var lessOptions = {
    options: {
        sourceMap: true,
        strictImports: false,
        sourceMapRootpath: '/',
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

/**
 * Compiles Less to CSS and generates necessary files if requested.
 */
module.exports = _.extend(themeOptions, lessOptions);
