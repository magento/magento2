/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var glob = require('glob'),
    fs = require('fs'),
    _ = require('underscore'),
    fst = require('../tools/fs-tools'),
    pc = require('../configs/path');

module.exports = {
    readFiles: function (paths) {
        var data = [];

        _.each(paths, function (path) {
            data = _.union(data, fst.getData(path));
        });

        return data;
    },

    getFilesForValidate: function () {
        var blackListFiles = glob.sync(pc.static.blacklist + '*.txt'),
            whiteListFiles = glob.sync(pc.static.whitelist + '*.txt'),
            blackList = this.readFiles(blackListFiles).filter(this.isListEntryValid),
            whiteList = this.readFiles(whiteListFiles).filter(this.isListEntryValid),
            files = [],
            entireBlackList = [];

        fst.arrayRead(blackList, function (data) {
            entireBlackList = _.union(entireBlackList, data);
        });

        fst.arrayRead(whiteList, function (data) {
            files = _.difference(data, entireBlackList);
        });

        return files;
    },

    isListEntryValid: function(line) {
        line = line.trim();
        return line.length > 0 && line.startsWith('// ') !== true;
    },

    getFiles: function (file) {
        var files;

        if (file) {
            return file.split(',');
        }

        if (!fs.existsSync(pc.static.tmp)) {
            fst.write(pc.static.tmp, this.getFilesForValidate());
        }

        files = fst.getData(pc.static.tmp);
        if (files.length === 1 && files[0] === '') {
            files = [];
        }

        return files;
    }
};
