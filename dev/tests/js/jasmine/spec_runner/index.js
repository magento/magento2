/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var tasks = [],
    _ = require('underscore');

function init(grunt, options) {
    var _                   = require('underscore'),
        stripJsonComments   = require('strip-json-comments'),
        path                = require('path'),
        config,
        themes;
        
    config = grunt.file.read(__dirname + '/settings.json');
    config = stripJsonComments(config);
    config = JSON.parse(config);

    //themes = require(path.resolve(process.cwd(), config.themes));
    //TODO: MAGETWO-39843
    themes = {
        blank: {
            area: 'frontend',
            name: 'Magento/blank',
            locale: 'en_US',
            files: [
                'css/styles-m',
                'css/styles-l',
                'css/email',
                'css/email-inline'
            ],
            dsl: 'less'
        },
        backend: {
            area: 'adminhtml',
            name: 'Magento/backend',
            locale: 'en_US',
            files: [
                'css/styles-old',
                'css/styles'
            ],
            dsl: 'less'
        }
    }

    if (options.theme) {
        themes = _.pick(themes, options.theme);
    }

    tasks = Object.keys(themes);

    config.themes = themes;

    enableTasks(grunt, config);
}

function enableTasks(grunt, config) {
    var jasmine = require('./tasks/jasmine'),
        connect = require('./tasks/connect');

    jasmine.init(config);
    connect.init(config);

    grunt.initConfig({
        jasmine: jasmine.getTasks(),
        connect: connect.getTasks()
    });
}

function getTasks() {
    tasks = tasks.map(function (theme) {
        return [
            'connect:' + theme,
            'jasmine:' + theme
        ]
    });

    return _.flatten(tasks);
}

module.exports = {
    init: init,
    getTasks: getTasks
};
