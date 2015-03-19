/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// For performance use one level down: 'name/{,*/}*.js'
// If you want to recursively match all subfolders, use: 'name/**/*.js'
module.exports = function (grunt) {
    'use strict';

    var _ = require('underscore'),
        path = require('path');

    require('./dev/tools/grunt/tasks/mage-minify')(grunt);
    require('time-grunt')(grunt);

    require('load-grunt-config')(grunt, {
        configPath: path.join(process.cwd(), 'dev/tools/grunt/configs'),
        init: true,
        loadGruntTasks: {
            pattern: [
                'grunt-*',
                '!grunt-template-jasmine-requirejs'
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
         * Styles for backend theme
         */
        backend: [
            'less:backend',
            'replace:escapeCalc',
            'less:override'
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

        spec: [
            'specRunner:lib',
            'specRunner:backend',
            'specRunner:frontend'
        ],

        unit: [
            'jasmine:lib-unit',
            'jasmine:backend-unit',
            'jasmine:frontend-unit'
        ],

        integration: [
            'jasmine:lib-integration',
            'jasmine:backend-integration',
            'jasmine:frontend-integration'
        ],

        'legacy-build': [
            'mage-minify:legacy'
        ],

        'documentation-banners': [
            'usebanner:documentationCss',
            'usebanner:documentationLess',
            'usebanner:documentationHtml'
        ]
    }, function (task, name) {
        grunt.registerTask(name, task);
    });
};
