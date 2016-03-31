/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

module.exports = function (grunt) {
    'use strict';

    var fs = require('fs'),
        _ = require('underscore'),
        glob = require('glob'),
        fst = require('../tools/fs-tools'),
        pc = require('../configs/path'),
        removeFromFile = function (path, files) {
            var data = _.difference(fst.getData(path), files);

            fst.write(path, data);
        };

    grunt.registerTask('clean-black-list', function () {
        process.chdir(grunt.option('dir') || '.');

        var filesToRemove = grunt.option('file').split(','),
            files = glob.sync(pc.static.blacklist + '*.txt');

        _.each(files, function (file) {
            removeFromFile(file, filesToRemove);
        });
    });
};
