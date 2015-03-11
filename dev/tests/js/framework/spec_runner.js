/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Creates jasmine configuration object
 *
 * @param  {String} type - type of tests
 * @param  {String} dir - area dir
 * @param  {Number} port - port to run on
 * @return {Object}
 */
function buildConfig(type, dir, port) {
    'use strict';

    var isLib           = dir === 'lib',
        requireConfigs  = [
            '<%= path.spec %>/require.config.js',
            '<%= path.spec %>/' + type + '/config/global.js'
        ],
        specsRoot       = '<%= path.spec %>/' + type,
        specs           =  specsRoot + (isLib ? '/lib/**/*.js' : '/**/' + dir + '/**/*.js');
    
    if (!isLib) {
        requireConfigs.push('<%= path.spec %>/' + type + '/config/' + dir + '.js');
    }

    return {
        src: '<%= path.spec %>/shim.js',
        options: {
            host: 'http://localhost:' + port,
            specs: specs,
            templateOptions: {
                requireConfigFile: requireConfigs
            }
        }
    };
}

module.exports = function (grunt) {
    'use strict';

    var connect     = require('connect'),
        logger      = require('morgan'),
        serveStatic = require('serve-static'),
        fs          = require('fs'),
        root;

    root = __dirname
        .replace('/dev/tests/js/framework', '')
        .replace('\\dev\\tests\\js\\framework', '');

    grunt.registerMultiTask('specRunner', function () {
        var app = connect(),
            options,
            area,
            theme,
            share,
            middlewares;

        options = this.options({
            port: 3000,
            theme: null,
            areaDir: null,
            shareDir: null,
            enableLogs: false,
            middleware: null
        });

        area    = options.areaDir;
        share   = options.shareDir;
        theme   = options.theme;

        if (options.enableLogs) {
            app.use(logger('dev'));
        }

        app.use(function (req, res, next) {
            var url     = req.url,
                match   = url.match(/^\/([A-Z][^\/]+)_(\w+)\/(.+)$/),
                vendor,
                module,
                path,
                getModuleUrl,
                getThemeUrl;

            /**
             * Returns path to theme root folder
             *
             * @return {String}
             */
            function themeRoot() {
                return [
                    '/app/design',
                    area,
                    vendor,
                    theme
                ].join('/');
            }

            /**
             * Based on 'thematic' parameter, returnes either path to theme's lib,
             *     or 'lib/web'.
             *
             * @param  {Boolean} thematic
             * @return {String}
             */
            function lib(thematic) {
                return thematic ? themeRoot() + '/web' : '/lib/web';
            }

            if (match !== null) {
                vendor  = match[1];
                module  = match[2];
                path    = match[3];

                /**
                 * Assembles modular path. If 'shared' flag provided and is truthy,
                 *     will use share dir instead of area one.
                 *
                 * @param  {Boolean} shared
                 * @return {String}
                 */
                getModuleUrl = function (shared) {
                    return [
                        '/app/code',
                        vendor,
                        module,
                        'view',
                        !!shared ? share : area,
                        'web',
                        path
                    ].join('/');
                };

                /**
                 * Assembles theme modular path.
                 *
                 * @return {String}
                 */
                getThemeUrl = function () {
                    return [
                        themeRoot(),
                        vendor + '_' + module,
                        'web',
                        path
                    ].join('/');
                };

                url = exists(url = getThemeUrl()) ?
                    url :
                    exists(url = getModuleUrl()) ?
                        url : getModuleUrl(true);

            } else if (canModify(url)) {
                url = (exists(url = lib(true)) ? url : lib()) + req.url;
            }

            req.url = url;

            next();
        });

        if (options.middleware && typeof options.middleware === 'function') {
            middlewares = options.middleware(connect, options);

            if (Array.isArray(middlewares)) {
                middlewares.forEach(function (middleware) {
                    app.use(middleware);
                });
            }
        }

        app.use(serveStatic(root));

        app.listen(options.port);
    });

    /**
     * Defines if passed file path exists
     *
     * @param  {String} path
     * @return {Boolean}
     */
    function exists(path) {
        return fs.existsSync(root + path);
    }

    /**
     * Restricts url's which lead to '/_SpecRunner.html', '/dev/tests' or '.grunt' folders from being modified
     *
     * @param  {String} url
     * @return {Boolean}
     */
    function canModify(url) {
        return url.match(/^\/(\.grunt)|(dev\/tests)|(dev\\tests)|(_SpecRunner\.html)/) === null;
    }

    return { configure: buildConfig };
};
