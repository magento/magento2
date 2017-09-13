/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo  = require('./combo'),
    themes = require('../tools/files-router').get('themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        'options': {
            livereload: true
        },
        'files': [
            '<%= combo.autopath(\''+name+'\', path.pub) %>/**/*.less'
        ],
        'tasks': 'less:' + name
    };
});

var watchOptions = {
    'setup': {
        'files': '<%= path.less.setup %>/**/*.less',
        'tasks': 'less:setup'
    },
    'updater': {
        'options': {
            livereload: true
        },
        'files': '<%= path.less.updater %>/**/*.less',
        'tasks': 'less:updater'
    },
    'reload': {
        'files': '<%= path.pub %>/**/*.css',
        'options': {
            livereload: true
        }
    }
};

module.exports = _.extend(themeOptions, watchOptions);
