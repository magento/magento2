/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
'use strict';

var fs = require('fs'),
    glob = require('glob'),
    nl = (function () {
        if (process.platform === 'win32') {
            return '\r\n';
        }

        return '\n';
    })();

module.exports = {
    getData: function (filePath) {
        return this.parseToReadData(fs.readFileSync(filePath));
    },
    write: function (file, data) {
        fs.writeFileSync(file, this.parseToWriteData(data));
        console.log('The file was saved!');
    },

    read: function (filePath) {
        console.log('Collect data from ' + filePath + ': Start!');

        return glob.sync(filePath);
    },

    arrayRead: function (pathArr, callback) {
        var len = pathArr.length,
            data = [],
            i = 0;

        for (; i < len; i++) {
            data = data.concat(this.read(pathArr[i]));
            console.log('Collect data from ' + pathArr[i] + ': Finish!');
        }
        callback(data);
    },

    parseToReadData: function (data) {
        var result = data.toString().split(nl);

        result.pop();

        return result;
    },

    parseToWriteData: function (data) {
        data = data.join(nl) + nl;

        return data;
    }
};
