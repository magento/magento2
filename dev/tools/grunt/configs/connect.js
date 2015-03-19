/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var serveStatic = require('serve-static'),
    _           = require('underscore'),
    ignoredPaths,
    middleware,
    themes,
    tasks,
    port = 8000;

ignoredPaths = [
    /^\/_SpecRunner.html/,
    /^\/dev\/tests/,
    /^\/.grunt/
];

function serveAsIs(path) {
    return ignoredPaths.some(function (ignoredPath) {
        return ignoredPath.test(path);
    });
}

middleware = function (connect, options, middlewares) {
    middlewares.unshift(function (req, res, next) {
        var url = req.url,
            server = serveStatic(process.cwd());
            
        if (serveAsIs(url)) {
            return server.apply(null, arguments);
        }

        return next();
    });

    return middlewares;
}

themes = require('./themes');

tasks = {};

_.each(themes, function (config, theme) {
    tasks[theme] = {
        options: {
            base: 'pub/static/' + config.area + '/' + config.name + '/' + config.locale,
            port: port++,
            middleware: middleware
        }
    }
});

module.exports = tasks;