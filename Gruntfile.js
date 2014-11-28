// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// If you want to recursively match all subfolders, use:
// 'test/spec/**/*.js'

module.exports = function (grunt) {

    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

    // Configurable paths and file names
    var config = {
        pub: 'pub',
        var: 'var',
        doc: {
            path: 'lib/web/css/docs',
            styleName: 'docs'
        }
    };

    // Define the configuration for all the tasks
    grunt.initConfig({

        // Project settings
        config: config,

        // Empties folders to start fresh
        clean: {
            var: {
                files: [{
                    dot: true,
                    src: [
                        '<%= config.var %>/cache/*',
                        '<%= config.var %>/generation/*',
                        '<%= config.var %>/log/*',
                        '<%= config.var %>/maps/*',
                        '<%= config.var %>/page_cache/*',
                        '<%= config.var %>/tmp/*',
                        '<%= config.var %>/view/*',
                        '<%= config.var %>/view_preprocessed/*'
                    ]
                }]
            },
            pub: {
                files: [{
                    dot: true,
                    src: [
                        '<%= config.pub %>/static/frontend/*',
                        '<%= config.pub %>/static/adminhtml/*'
                    ]
                }]
            }
        },

        // Compiles Less to CSS and generates necessary files if requested
        less: {
            documentation: {
                options: {
                    sourceMap: false,
                    ieCompat: false,
                    paths: ['<%= config.doc.path %>/source/']
                },
                files: {
                    '<%= config.doc.path %>/<%= config.doc.styleName %>.css': "<%= config.doc.path %>/source/<%= config.doc.styleName %>.less"
                }
            }
        },

        styledocco: {
            documentation: {
                options: {
                    name: 'Magento UI Library',
                    verbose: true,
                    include: [
                        '<%= config.doc.path %>/<%= config.doc.styleName %>.css',
                        'lib/web/jquery/jquery.min.js',
                        'lib/web/jquery/jquery-ui.min',
                        '<%= config.doc.path %>/source/js/dropdown.js'
                    ]
                },
                files: {
                    '<%= config.doc.path %>': '<%= config.doc.path %>/source'
                }
            }
        }

    });

    grunt.registerTask('default', []);

    grunt.registerTask('cleanup', [
        'clean:var',
        'clean:pub'
    ]);

    grunt.registerTask('documentation', [
        'less:documentation',
        'styledocco:documentation',
        'cleanup'
    ]);
};
