/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

module.exports = function (grunt) {
    'use strict';

    var exec    = require('child_process').execSync,
        spawn   = require('child_process').spawn,
        log     = grunt.log.write,
        ok      = grunt.log.ok,
        error   = grunt.log.error;

    grunt.registerTask('deploy', function (grunt) {
        var deployLog,
            deploy,
            done = this.async();

        log('Cleaning "pub/static"...');
        exec('rm -rf pub/static/*');
        ok('"pub/static" is empty.');

        log('Deploying Magento application...');
        deploy = spawn('php', ['dev/tools/Magento/Tools/View/deploy.php']);
        
        deploy.stdout.on('data', function (data) {
            log(data);
        });

        deploy.stdin.on('data', function (data) {
            error(data);
        });

        deploy.on('close', done);
    });
}