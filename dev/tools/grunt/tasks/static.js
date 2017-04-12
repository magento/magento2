/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

module.exports = function (grunt) {
    'use strict';

    var pc = require('../configs/path'),
        fs = require('fs'),
        cvf = require('../tools/collect-validation-files'),
        setConfig = function (task, target, data) {
            var config = grunt.config.get(task);

            config[target].src = data;
            grunt.config.set(task, config);
        };

    grunt.registerTask('static', function (target) {
        var currentTarget = target || 'test',
            file = grunt.option('file'),
            tasks = [
                'eslint:' + currentTarget,
                'jscs:' + currentTarget
            ];

        setConfig('eslint', currentTarget, cvf.getFiles(file));
        setConfig('jscs', currentTarget, cvf.getFiles(file));
        grunt.option('force', true);
        grunt.task.run(tasks);

        if (!grunt.option('file')) {
            fs.unlinkSync(pc.static.tmp);
        }
    });
};
