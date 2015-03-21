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
    var requireJsConfigs,
        specs,
        area = config.area,
        vendorThemePath = config.name;

    requireJsConfigs = [
        'pub/static/_requirejs/' + area + '/' + vendorThemePath + '/' + config.locale + '/requirejs-config.js',
        root + '/require.conf.js',
        root + '/tests/lib/**/*.conf.js',
        root + '/tests/app/code/**/base/**/*.conf.js',
        root + '/tests/app/code/**/' + area + '/**/*.conf.js',
        root + '/tests/app/design/' + area + '/' + vendorThemePath + '/**/*.conf.js'
    ];

    specs = [
        root + '/tests/lib/**/*.test.js',
        root + '/tests/app/code/**/base/**/*.test.js',
        root + '/tests/app/code/**/' + area + '/**/*.test.js',
        root + '/tests/app/design/' + area + '/' + vendorThemePath + '/' + theme + '/**/*.test.js'
    ];

    tasks[theme] = {
        src: requireJsConfigs,
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