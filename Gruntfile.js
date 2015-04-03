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
        loadGruntTasks: {
            pattern: [
                'grunt-*'
            ]
        }
    });

    _.each({
        /**
         * Assembling tasks.
         * ToDo UI: define default tasks.
         */
        default: function () {
            grunt.log.subhead('I\'m default task and at the moment I\'m empty, sorry :/');
        },

        /**
         * Production preparation task.
         */
        prod: function (component) {
            if (component === 'setup') {
                grunt.task.run([
                    'less:' + component,
                    'autoprefixer:' + component,
                    'cssmin:' + component,
                    'usebanner:' + component
                ]);
            }

            if (typeof component === 'undefined') {
                grunt.log.subhead('Tip: Please make sure that u specify prod subtask. By default prod task do nothing');
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
            'less:documentation',
            'styledocco:documentation',
            'clean:var',
            'clean:pub'
        ],

        'legacy-build': [
            'mage-minify:legacy'
        ],

        'documentation-banners': [
            'usebanner:documentationCss',
            'usebanner:documentationLess',
            'usebanner:documentationHtml'
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
