/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var theme = require('../tools/files-router').get('themes'),
    path = require('./path');

/**
 * Define Combos for repetitive code.
 */
module.exports = {
    collector: function (themeName) {
        var cmdPlus = /^win/.test(process.platform) ? ' & ' : ' && ',
            command = 'grunt --force clean:' + themeName + cmdPlus;

        command = command + 'php bin/magento dev:source-theme:deploy ' +
            theme[themeName].files.join(' ') +
            ' --type=less' +
            ' --locale=' + theme[themeName].locale +
            ' --area=' + theme[themeName].area +
            ' --theme=' + theme[themeName].name;

        return command;
    },

    autopath: function (themeName, folder) {
        return folder +
            theme[themeName].area + '/' +
            theme[themeName].name + '/' +
            theme[themeName].locale + '/';
    },

    lessFiles: function (themeName) {
        var lessStringArray = [],
            cssStringArray = [],
            lessFiles = {},
            i = 0;

        for (i; i < theme[themeName].files.length; i++) {
            cssStringArray[i] = path.pub +
            theme[themeName].area + '/' +
            theme[themeName].name + '/' +
            theme[themeName].locale + '/' +
            theme[themeName].files[i] + '.css';

            lessStringArray[i] = path.pub +
            theme[themeName].area + '/' +
            theme[themeName].name + '/' +
            theme[themeName].locale + '/' +
            theme[themeName].files[i] + '.less';

            lessFiles[cssStringArray[i]] = lessStringArray[i];
        }

        return lessFiles;
    }
};
