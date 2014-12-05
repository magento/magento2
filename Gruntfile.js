// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// If you want to recursively match all subfolders, use:
// 'test/spec/**/*.js'

'use strict';

module.exports = function (grunt) {

    // Require
    // --------------------------------------

    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

    // Configurable paths and file names
    // --------------------------------------

    var config = {
        path: {
            pub: 'pub',
            var: 'var',
            css: {
                blank: 'pub/static/frontend/Magento/blank/en_US/css',
                luma: 'pub/static/frontend/Magento/luma/en_US/css'
            },
            less: {
                lib: 'lib/web/css/',
                blank: 'app/design/frontend/Magento/blank',
                luma: 'app/design/frontend/luma'
            },
            doc: 'lib/web/css/docs'
        },
        doc: {
            styleName: 'docs'
        }
    };

    // Define the configuration for all the tasks
    // --------------------------------------

    grunt.initConfig({

        // Project settings
        config: config,

        // Watches files for changes and runs tasks based on the changed files
        watch: {
            less: {
                files: [
                    '<%= config.path.less.lib %>/{,*/}*.less',
                    '<%= config.path.less.blank %>/{,*/,*/*/,*/*/*/,*/*/*/*/}*.less', // ToDo UI: find out how to do it recursive
                    '<%= config.path.less.luma %>/{,*/,*/*/,*/*/*/,*/*/*/*/}*.less'
                ],
                tasks: ['styles']
            }
        },

        // Empties folders to start fresh
        clean: {
            var: {
                files: [{
                    dot: true,
                    src: [
                        '<%= config.path.var %>/cache/*',
                        '<%= config.path.var %>/generation/*',
                        '<%= config.path.var %>/log/*',
                        '<%= config.path.var %>/maps/*',
                        '<%= config.path.var %>/page_cache/*',
                        '<%= config.path.var %>/tmp/*',
                        '<%= config.path.var %>/view/*',
                        '<%= config.path.var %>/view_preprocessed/*'
                    ]
                }]
            },
            pub: {
                files: [{
                    dot: true,
                    src: [
                        '<%= config.path.pub %>/static/frontend/*',
                        '<%= config.path.pub %>/static/adminhtml/*'
                    ]
                }]
            }
        },

        // Compiles Less to CSS and generates necessary files if requested
        less: {
            options: {
                sourceMap: true,
                sourceMapRootpath: '/',
                dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
                ieCompat: false
            },
            blank: {
                files: {
                    '<%= config.path.css.blank %>/styles-m.css': '<%= config.path.css.blank %>/styles-m.less',
                    '<%= config.path.css.blank %>/styles-l.css': '<%= config.path.css.blank %>/styles-l.less'
                }
            },
            luma: {
                files: {
                    '<%= config.path.css.luma %>/styles-m.css': '<%= config.path.css.luma %>/styles-m.less',
                    '<%= config.path.css.luma %>/styles-l.css': '<%= config.path.css.luma %>/styles-l.less'
                }
            },
            documentation: {
                files: {
                    '<%= config.path.doc %>/<%= config.doc.styleName %>.css': "<%= config.path.doc %>/source/<%= config.doc.styleName %>.less"
                }
            }
        },

        styledocco: {
            documentation: {
                options: {
                    name: 'Magento UI Library',
                    verbose: true,
                    include: [
                        '<%= config.path.doc %>/<%= config.doc.styleName %>.css'
                        //'lib/web/jquery/jquery.min.js',
                        //'lib/web/jquery/jquery-ui.min',
                        //'<%= config.path.doc %>/source/js/dropdown.js'
                    ]
                },
                files: {
                    '<%= config.path.doc %>': '<%= config.path.doc %>/source' // Todo UI: Check out JS for Styledocco
                }
            }
        }

    });

    // Default task
    // --------------------------------------
    grunt.registerTask('default', []); // ToDo UI: define default tasks

    // Clean var & pub folders
    grunt.registerTask('cleanup', [
        'clean:var',
        'clean:pub'
    ]);

    // Compile all styles
    // --------------------------------------
    grunt.registerTask('styles', [
        'less:blank',
        'less:luma'
    ]);

    // Compile blank styles
    grunt.registerTask('styles blank', [
        'less:blank'
    ]);

    // Compile luma styles
    grunt.registerTask('styles luma', [
        'less:luma'
    ]);

    // Documentation
    // --------------------------------------
    grunt.registerTask('documentation', [
        'less:documentation',
        'styledocco:documentation',
        'cleanup'
    ]);
};
