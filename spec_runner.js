'use strict';

module.exports = function(grunt) {
    var connect     = require('connect'),
        logger      = require('morgan'),
        serveStatic = require('serve-static'),
        fs          = require('fs');

    function canModify(url) {
        return url.match(/^\/(\.grunt)|(dev)|(_SpecRunner\.html)/) === null;
    };

    grunt.registerMultiTask('specRunner', function(grunt) {
        var app = connect(),
            options,
            area,
            share,
            moduleRoot,
            libPath;

        options = this.options({
            port: 3000,
            areaDir: 'adminhtml',
            shareDir: 'base',
            enableLogs: false
        });

        area        = options.areaDir;
        share       = options.shareDir;
        moduleRoot  = '/app/code/Magento/';
        libPath     = '/lib/web';

        if (options.enableLogs) {
            app.use(logger('dev'));
        }

        function assembleUrl(module, area, path) {
            return moduleRoot + module + '/view/' + area + '/web/' + path;
        }

        app.use(function(req, res, next) {
            var url = req.url,
                match = url.match(/^\/Magento_([^\/]+)(\/.+)$/),
                module,
                path,
                exist;

            if (match !== null) {
                module  = match[1];
                path    = match[2];
                url     = assembleUrl(module, area, path),
                exist   = fs.existsSync(url);

                if (!exist) {
                    url = assembleUrl(module, share, path);
                }

            } else if (canModify(url)) {
                url = libPath + url;
            }

            req.url = url;

            next();
        });

        app.use(serveStatic(__dirname));

        app.listen(options.port);
    });
}