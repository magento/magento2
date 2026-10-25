/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

var tasks = {};

function init(config) {
    'use strict';

    var serveStatic = require('serve-static'),
        _           = require('underscore'),
        ignoredPaths, middleware, themes, port;

    port         = config.port;
    themes       = config.themes;
    ignoredPaths = config.server.serveAsIs;

    function serveAsIs(requestUrl) {
        return ignoredPaths.some(function (ignoredPath) {
            return new RegExp(ignoredPath).test(requestUrl);
        });
    }

    middleware = function (connect, options, middlewares) {
        var server = serveStatic(process.cwd(), {
            dotfiles: 'allow'
        });

        middlewares.unshift(function (req, res, next) {
            var url = req.url;

            if (serveAsIs(url)) {
                return server.apply(null, arguments);
            }

            return next();
        });

        return middlewares;
    };

    _.each(themes, function (themeData, themeName) {
        var options = {
            base: _.template(config.server.base)(themeData),
            port: port++,
            middleware: middleware
        };

        _.defaults(options, config.server.options);

        tasks[themeName] = { options: options };
    });
}

function getTasks() {
    'use strict';

    return tasks;
}

module.exports = {
    init: init,
    getTasks: getTasks
};
