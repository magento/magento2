/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
module.exports = function (grunt) {
    'use strict';

    var exec    = require('child_process').execSync,
        spawn   = require('child_process').spawn,
        log     = grunt.log.write,
        ok      = grunt.log.ok,
        error   = grunt.log.error;

    grunt.registerTask('deploy', function () {
        var deploy,
            done = this.async();

        log('Cleaning "pub/static"...');
        exec('rm -rf pub/static/*');
        ok('"pub/static" is empty.');

        log('Deploying Magento application...');
        deploy = spawn('php', ['bin/magento', 'setup:static-content:deploy']);

        deploy.stdout.on('data', function (data) {
            log(data);
        });

        deploy.stdin.on('data', function (data) {
            error(data);
        });

        deploy.on('close', done);
    });
};
