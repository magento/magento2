/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var tasks = {},
    _ = require('underscore');

function init(config) {
    var grunt  = require('grunt'),
        expand = grunt.file.expand.bind(grunt.file),
        staticMode = 'quick',
        themes, root, staticDir, baseUrl, mapFile, host, port, files, requireJs;

    root         = config.root;
    staticDir       = config.static;
    port         = config.port;
    files        = config.files;
    themes       = config.themes;

    _.each(themes, function (themeData, themeName) {
        var specs,
            configs,
            render;

        _.extend(themeData, {
            root: root,
            static: staticDir
        });

        host    = _.template(config.host)({
            port: port++
        });
        render  = renderTemplate.bind(null, themeData);
        mapFile = renderTemplate(themeData, files.compactMap);
        baseUrl = renderTemplate(themeData, files.requireBaseUrl);

        if (grunt.file.exists(mapFile)) {
            staticMode = 'compact';
        }

        if (config.singleTest) {
            files.specs = [config.singleTest];
        }

        specs   = files.specs.map(render);
        specs   = expand(specs).map(cutJsExtension);
        configs = files.requirejsConfigs[staticMode].map(render);
        requireJs = renderTemplate(themeData, files.requireJs[staticMode]);

        tasks[themeName] = {
            src: configs,
            options: {
                host: host,
                template: render(files.template),
                templateOptions: {
                    baseUrl: baseUrl
                },
                vendor: requireJs,
                junit: {
                    path: "var/log/js-unit/",
                    consolidate: true
                },

                /**
                 * @todo rename "helpers" to "specs" (implies overriding grunt-contrib-jasmine code)
                 */
                helpers: specs
            }
        }
    });
}

function renderTemplate(data, template) {
    return _.template(template)(data);
}

function cutJsExtension(path) {
    return path.replace(/\.js$/, '');
}

function getTasks() {
    return tasks;
}

module.exports = {
    init: init,
    getTasks: getTasks
};
