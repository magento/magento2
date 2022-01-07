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
        var currentTarget = target || 'file',
            file = grunt.option('file'),
            tasks = [
                'continue:on',
                'eslint:' + currentTarget,
                'continue:off',
                'continue:fail-on-warning'
            ];

        setConfig('eslint', currentTarget, cvf.getFiles(file));
        grunt.task.run(tasks);

        if (!grunt.option('file')) {
            fs.unlinkSync(pc.static.tmp);
        }
    });
};
