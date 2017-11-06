/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            blackList = this.readFiles(blackListFiles),
            whiteList = this.readFiles(whiteListFiles),
            files = [];

        console.log("Lists overview");
        console.log(JSON.stringify(blackList));
        console.log(JSON.stringify(whiteList));
        console.log("WhiteListLoop");
        fst.arrayRead(whiteList, function (data) {
            console.log(data);
            files = _.difference(data, blackList);
        });
        console.log("Result");
        console.log(JSON.stringify(files));
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
