/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// For performance use one level down: 'name/{,*/}*.js'
// If you want to recursively match all subfolders, use: 'name/**/*.js'
module.exports = function (grunt) {
    'use strict';

    var _ = require('underscore'),
        path = require('path');

    [
        './dev/tools/grunt/tasks/mage-minify',
        './dev/tools/grunt/tasks/deploy',
        'time-grunt'
    ].forEach(function (task) {
        require(task)(grunt);
    });

    require('load-grunt-config')(grunt, {
        configPath: path.join(process.cwd(), 'dev/tools/grunt/configs'),
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

        spec: [
            'connect:frontend',
            'jasmine:frontend'
        ]
    }, function (task, name) {
        grunt.registerTask(name, task);
    });
};
