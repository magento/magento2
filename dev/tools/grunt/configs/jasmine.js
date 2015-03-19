/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var grunt = require('grunt'),
    _     = require('underscore'),
    expand = grunt.file.expand.bind(grunt.file),
    themes,
    tasks,
    root = 'dev/tests/js/jasmine',
    host = 'http://localhost',
    port = 8000;

function cutExtension(name) {
    return name.replace(/\.js$/, '');
}

themes = require('./themes');

tasks = {};

_.each(themes, function (config, theme) {
    var requireJsConfig = root + '/testsuite/' + config.area + '/' + config.name + '/require.config.js',
        specs;

    specs = [
        root + '/testsuite/' + config.area + '/' + config.name + '/**/*.test.js',
        '!' + requireJsConfig
    ];

    tasks[theme] = {
        src: requireJsConfig,
        options: {
            host: host + ':' + port++,
            template: root + '/spec_runner.html',
            vendor: 'requirejs/require.js',

            /**
             * @todo rename "helpers" to "specs" (implies overriding grunt-contrib-jasmine code)
             */
            helpers: expand(specs).map(cutExtension)
        }
    }
});

module.exports = tasks;