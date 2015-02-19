/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

// For performance use one level down: 'name/{,*/}*.js'
// If you want to recursively match all subfolders, use: 'name/**/*.js'
module.exports = function (grunt) {
    'use strict';

    //  Required plugins
    //  _____________________________________________

    var specRunner = require('./dev/tests/js/framework/spec_runner')(grunt),
        svgo = require('imagemin-svgo');

    require('./dev/tools/grunt/tasks/mage-minify')(grunt);

    //  Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    //  Load grunt tasks automatically
    require('load-grunt-tasks')(grunt, {
        pattern: ['grunt-*', '!grunt-template-jasmine-requirejs']
    });

    //  Configuration
    //  _____________________________________________

    //  Define Paths
    //  ---------------------------------------------

    var path = {
        pub: 'pub/static/',
        tmpLess: 'var/view_preprocessed/less/',
        tmpSource: 'var/view_preprocessed/source/',
        tmp: 'var',
        css: {
            setup: 'setup/pub/styles'
        },
        less: {
            setup: 'setup/view/styles'
        },
        uglify: {
            legacy: 'lib/web/legacy-build.min.js'
        },
        doc: 'lib/web/css/docs',
        spec: 'dev/tests/js/spec'
    };

    //  Define Themes
    //  ---------------------------------------------

    var theme = {
        blank: {
            area: 'frontend',
            name: 'Magento/blank',
            locale: 'en_US',
            files: [
                'css/styles-m',
                'css/styles-l'
            ]
        },
        luma: {
            area: 'frontend',
            name: 'Magento/luma',
            locale: 'en_US',
            files: [
                'css/styles-m',
                'css/styles-l'
            ]
        },
        backend: {
            area: 'adminhtml',
            name: 'Magento/backend',
            locale: 'en_US',
            files: [
                'css/styles-old',
                'css/styles',
                'css/pages',
                'css/admin'
            ]
        }
    };

    //  Define Combos for repetitive code
    //  ---------------------------------------------

    var combo = {
        // Run php script for gathering less simlynks into pub directory
        collector: function (themeName) {
            var cmdPlus = (/^win/.test(process.platform) == true) ? ' & ' : ' && ';
            var command = 'grunt --force clean:' + themeName + cmdPlus;
            command = command + 'php -f dev/tools/Magento/Tools/Webdev/less.php --'
            + ' --locale=' + theme[themeName].locale
            + ' --area=' + theme[themeName].area
            + ' --theme=' + theme[themeName].name
            + ' --files=' + theme[themeName].files.join(',');
            return command;
        },
        autopath: function (themeName) {
            return path.pub
                + theme[themeName].area + '/'
                + theme[themeName].name + '/'
                + theme[themeName].locale + '/';
        },
        lessFiles: function (themeName) {
            var lessStringArray = [],
                cssStringArray = [],
                lessFiles = {},
                i = 0;
            for (i; i < theme[themeName].files.length; i++) {
                cssStringArray[i] = path.pub
                + theme[themeName].area + '/'
                + theme[themeName].name + '/'
                + theme[themeName].locale + '/'
                + theme[themeName].files[i] + '.css';
                lessStringArray[i] = path.pub
                + theme[themeName].area + '/'
                + theme[themeName].name + '/'
                + theme[themeName].locale + '/'
                + theme[themeName].files[i] + '.less';

                lessFiles[cssStringArray[i]] = lessStringArray[i];
            }
            return lessFiles;
        }
    };

    //  Tasks
    //  _____________________________________________

    grunt.initConfig({

        //  Project settings
        path: path,
        theme: theme,
        combo: combo,

        //  Execution into cmd
        //  ---------------------------------------------

        exec: {
            blank: {
                cmd: function () {
                    return combo.collector('blank');
                }
            },
            luma: {
                cmd: function () {
                    return combo.collector('luma');
                }
            },
            backend: {
                cmd: function () {
                    return combo.collector('backend');
                }
            },
            all: {
                cmd: function () {
                    var command = '',
                        cmdPlus = (/^win/.test(process.platform) == true) ? ' & ' : ' && ',
                        themes = Object.keys(theme),
                        i = 0;
                    for (i; i < themes.length; i++) {
                        command += combo.collector(themes[i]) + cmdPlus;
                    }
                    return 'echo ' + command;
                }
            }
        },

        //  Cleanup temporary files
        //  ---------------------------------------------

        clean: {
            var: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/cache/**/*',
                        '<%= path.tmp %>/generation/**/*',
                        '<%= path.tmp %>/log/**/*',
                        '<%= path.tmp %>/maps/**/*',
                        '<%= path.tmp %>/page_cache/**/*',
                        '<%= path.tmp %>/tmp/**/*',
                        '<%= path.tmp %>/view/**/*',
                        '<%= path.tmp %>/view_preprocessed/**/*'
                    ]
                }]
            },
            pub: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.pub %>frontend/**/*',
                        '<%= path.pub %>adminhtml/**/*'
                    ]
                }]
            },
            styles: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/view_preprocessed/**/*',
                        '<%= path.tmp %>/cache/**/*',
                        '<%= path.pub %>frontend/**/*.less',
                        '<%= path.pub %>frontend/**/*.css',
                        '<%= path.pub %>adminhtml/**/*.less',
                        '<%= path.pub %>adminhtml/**/*.css'
                    ]
                }]
            },
            // Layout & templates cleanup
            markup: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/cache/**/*',
                        '<%= path.tmp %>/generation/**/*',
                        '<%= path.tmp %>/page_cache/**/*'
                    ]
                }]
            },
            js: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.pub %>**/*.js',
                        '<%= path.pub %>**/*.html',
                        '<%= path.pub %>_requirejs/**/*'
                    ]
                }]
            },
            blank: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/cache/**/*',
                        '<%= combo.autopath("blank", "pub") %>**/*',
                        '<%= combo.autopath("blank", "tmpLess") %>**/*',
                        '<%= combo.autopath("blank", "tmpSource") %>**/*'
                    ]
                }]
            },
            backend: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/cache/**/*',
                        '<%= combo.autopath("backend", "pub") %>**/*',
                        '<%= combo.autopath("backend", "tmpLess") %>**/*',
                        '<%= combo.autopath("backend", "tmpSource") %>**/*'
                    ]
                }]
            },
            luma: {
                force: true,
                files: [{
                    force: true,
                    dot: true,
                    src: [
                        '<%= path.tmp %>/cache/**/*',
                        '<%= combo.autopath("luma", "pub") %>**/*',
                        '<%= combo.autopath("luma", "tmpLess") %>**/*',
                        '<%= combo.autopath("luma", "tmpSource") %>**/*'
                    ]
                }]
            }
        },

        //  Compiles Less to CSS and generates necessary files if requested
        //  ---------------------------------------------
        less: {
            options: {
                sourceMap: true,
                strictImports: false,
                sourceMapRootpath: '/',
                dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
                ieCompat: false
            },
            backend: {
                files: combo.lessFiles('backend')
            },
            blank: {
                files: combo.lessFiles('blank')
            },
            luma: {
                files: combo.lessFiles('luma')
            },
            setup: {
                files: {
                    '<%= path.css.setup %>/setup.css': "<%= path.less.setup %>/setup.less"
                }
            },
            documentation: {
                files: {
                    '<%= path.doc %>/docs.css': "<%= path.doc %>/source/docs.less"
                }
            }
        },

        //  Styles minify
        //  ---------------------------------------------

        cssmin: {
            options: {
                report: 'gzip',
                keepSpecialComments: 0
            },
            setup: {
                files: {
                    '<%= path.css.setup %>/setup.css': '<%= path.css.setup %>/setup.css'
                }
            }
        },

        //  Styles autoprefixer
        //  ---------------------------------------------

        autoprefixer: {
            options: {
                browsers: [
                    'last 2 versions',
                    'ie 9'
                ]
            },
            setup: {
                src: '<%= path.css.setup %>/setup.css'
            }
        },

        //  Concatenation
        //  ---------------------------------------------

        concat: {
            options: {
                stripBanners: true,
                banner: '/**\n * Copyright © <%= grunt.template.today("yyyy") %> Magento. All rights reserved.\n * See COPYING.txt for license details.\n */\n'
            },
            setup: {
                src: '<%= path.css.setup %>/setup.css',
                dest: '<%= path.css.setup %>/setup.css'
            }
        },


        //  Watches files for changes and runs tasks based on the changed files
        //  ---------------------------------------------

        watch: {
            backend: {
                files: [
                    '<%= combo.autopath("backend","pub") %>/**/*.less'
                ],
                tasks: 'less:backend'
            },
            blank: {
                files: [
                    '<%= combo.autopath("blank","pub") %>/**/*.less'
                ],
                tasks: 'less:blank'
            },
            luma: {
                files: [
                    '<%= combo.autopath("luma","pub") %>/**/*.less'
                ],
                tasks: 'less:luma'
            },
            setup: {
                files: '<%= path.less.setup %>/**/*.less',
                tasks: 'less:setup'
            }
        },

        // Images optimization
        imagemin: {
            png: {
                options: {
                    optimizationLevel: 7
                },
                files: [
                    {
                        expand: true,
                        src: ['**/*.png'],
                        ext: '.png'
                    }
                ]
            },
            jpg: {
                options: {
                    progressive: true
                },
                files: [
                    {
                        expand: true,
                        src: ['**/*.jpg'],
                        ext: '.jpg'
                    }
                ]
            },
            gif: {
                files: [
                    {
                        expand: true,
                        src: ['**/*.gif'],
                        ext: '.gif'
                    }
                ]
            },
            svg: {
                options: {
                    use: [svgo()]
                },
                files: [
                    {
                        expand: true,
                        src: ['**/*.svg'],
                        ext: '.svg'
                    }
                ]
            }
        },

        'mage-minify': {
            legacy: {
                options: {
                    type: 'yui-js',
                    tempPath: 'var/cache/',
                    options: ['--nomunge=true']
                },
                files: {
                    '<%= config.path.uglify.legacy %>': [
                        'lib/web/prototype/prototype.js',
                        'lib/web/prototype/window.js',
                        'lib/web/scriptaculous/builder.js',
                        'lib/web/scriptaculous/effects.js',
                        'lib/web/lib/ccard.js',
                        'lib/web/prototype/validation.js',
                        'lib/web/varien/js.js',
                        'lib/web/mage/adminhtml/varienLoader.js',
                        'lib/web/mage/adminhtml/tools.js'
                    ]
                }
            }
        },

        //

        styledocco: {
            documentation: {
                options: {
                    name: 'Magento UI Library',
                    verbose: true,
                    include: [
                        '<%= path.doc %>/docs.css' // Todo UI: Check out JS for Styledocco
                        //'lib/web/jquery/jquery.min.js',
                        //'lib/web/jquery/jquery-ui.min',
                        //'<%= path.doc %>/source/js/dropdown.js'
                    ]
                },
                files: {
                    '<%= path.doc %>': '<%= path.doc %>/source'
                }
            }
        },

        specRunner: {
            options: {
                shareDir: 'base'
            },
            backend: {
                options: {
                    port: 8000,
                    areaDir: 'adminhtml',
                    theme: 'backend'
                }
            },
            frontend: {
                options: {
                    port: 3000,
                    areaDir: 'frontend',
                    theme: 'blank'
                }
            },
            lib: {
                options: {
                    port: 8080
                }
            }
        },

        jasmine: {
            'options': {
                template: require('grunt-template-jasmine-requirejs'),
                ignoreEmpty: true
            },
            'lib-unit':               specRunner.configure('unit', 'lib', 8080),
            'lib-integration':        specRunner.configure('integration', 'lib', 8080),
            'backend-unit':           specRunner.configure('unit', 'adminhtml', 8000),
            'backend-integration':    specRunner.configure('integration', 'adminhtml', 8000),
            'frontend-unit':          specRunner.configure('unit', 'frontend', 3000),
            'frontend-integration':   specRunner.configure('integration', 'frontend', 3000)
        }
    });

    //  Assembling tasks
    //  _____________________________________________

    grunt.registerTask('default', function () { // ToDo UI: define default tasks
        grunt.log.subhead('I\'m default task and at the moment I\'m empty, sorry :/');
    });

    //  Refresh magento frontend & backend
    //  ---------------------------------------------

    grunt.registerTask('refresh', [
        'exec:all',
        'less:blank',
        'less:luma',
        'less:backend'
    ]);

    //  Creates build of a legacy files.
    //  Mostly prototype dependant libraries.
    //  ---------------------------------------------

    grunt.registerTask('legacy-build', [
        'mage-minify:legacy'
    ]);

    //  Documentation
    //  ---------------------------------------------

    grunt.registerTask('documentation', [
        'less:documentation',
        'styledocco:documentation',
        'clean:var',
        'clean:pub'
    ]);

    //  Production
    //  ---------------------------------------------

    grunt.registerTask('prod', function (component) {
        if (component === 'setup') {
            grunt.task.run([
                'less:' + component,
                'autoprefixer:' + component,
                'cssmin:' + component,
                'concat:' + component
            ]);
        }
        if (component == undefined) {
            grunt.log.subhead('Tip: Please make sure that u specify prod subtask. By default prod task do nothing');
        }
    });

    //  Tests
    //  ---------------------------------------------
    
    grunt.registerTask('spec', [
        'specRunner:lib',
        'specRunner:backend',
        'specRunner:frontend'
    ]);

    grunt.registerTask('unit', [
        'jasmine:lib-unit',
        'jasmine:backend-unit',
        'jasmine:frontend-unit'
    ]);

    grunt.registerTask('integration', [
        'jasmine:lib-integration',
        'jasmine:backend-integration',
        'jasmine:frontend-integration'
    ]);
};
