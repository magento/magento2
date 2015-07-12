/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// For performance use one level down: 'name/{,*/}*.js'
// If you want to recursively match all subfolders, use: 'name/**/*.js'
module.exports = function (grunt) {
    'use strict';

    var _ = require('underscore'),
        path = require('path'),
        configDir = './dev/tools/grunt/configs',
        taskDir = './dev/tools/grunt/tasks';

    [
        taskDir + '/mage-minify',
        taskDir + '/deploy',
        'time-grunt'
    ].forEach(function (task) {
        require(task)(grunt);
    });

    require('load-grunt-config')(grunt, {
        configPath: path.join(__dirname, configDir),
        init: true,
        jitGrunt: {
            staticMappings: {
                usebanner: 'grunt-banner'
            }
        }
    });

    _.each({
        /**
         * Assembling tasks.
         * ToDo: define default tasks.
         */
        default: function () {
            grunt.log.subhead('I\'m default task and at the moment I\'m empty, sorry :/');
        },

        /**
         * Production preparation task.
         */
        prod: function (component) {
            var tasks = [
                'less',
                'autoprefixer',
                'cssmin',
                'usebanner'
            ].map(function(task){
                return task + ':' + component;
            });

            if (typeof component === 'undefined') {
                grunt.log.subhead('Tip: Please make sure that u specify prod subtask. By default prod task do nothing');
            } else {
                grunt.task.run(tasks);
            }
        },

        /**
         * Refresh magento frontend & backend.
         */
        refresh: [
            'exec:all',
            'less:blank',
            'less:luma',
            'less:backend'
        ],

        /**
         * Documentation
         */
        documentation: [
            'replace:documentation',
            'less:documentation',
            'styledocco:documentation',
            'usebanner:documentationCss',
            'usebanner:documentationLess',
            'usebanner:documentationHtml',
            'clean:var',
            'clean:pub'
        ],

        'legacy-build': [
            'mage-minify:legacy'
        ],

        spec: function (theme) {
            var runner = require('./dev/tests/js/jasmine/spec_runner');

            runner.init(grunt, { theme: theme });

            grunt.task.run(runner.getTasks());
        }
    }, function (task, name) {
        grunt.registerTask(name, task);
    });
};
