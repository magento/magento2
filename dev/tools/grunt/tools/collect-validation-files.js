/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            blackList = this.readFiles(blackListFiles),
            whiteList = this.readFiles(whiteListFiles),
            files = [];

        fst.arrayRead(whiteList, function (data) {
            files = _.difference(data, blackList);
        });

        return files;
    },

    getFiles: function (file) {
        if (file) {
            return file.split(',');
        }

        if (!fs.existsSync(pc.static.tmp)) {
            fst.write(pc.static.tmp, this.getFilesForValidate());
        }

        return fst.getData(pc.static.tmp);
    }
};
