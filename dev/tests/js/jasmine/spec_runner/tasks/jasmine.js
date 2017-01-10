/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var tasks = {},
    _ = require('underscore');

function init(config) {
    var grunt  = require('grunt'),
        expand = grunt.file.expand.bind(grunt.file),
        themes, root, host, port, files;

    root         = config.root;
    port         = config.port;
    files        = config.files;
    themes       = config.themes;

    _.each(themes, function (themeData, themeName) {
        var specs,
            configs,
            render;

        _.extend(themeData, { root: root });

        host    = _.template(config.host)({ port: port++ });
        render  = renderTemplate.bind(null, themeData);

        if (config.singleTest) {
            files.specs = [config.singleTest];
        }

        specs   = files.specs.map(render);
        specs   = expand(specs).map(cutJsExtension);
        configs = files.requirejsConfigs.map(render);

        tasks[themeName] = {
            src: configs,
            options: {
                host: host,
                template: render(files.template),
                vendor: files.requireJs,
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
