/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var theme = require('./themes'),
    path  = require('./path'),
    _     = require('underscore');

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

    cssFiles: function (themeName) {
        var t = theme[themeName],
            files = [];

        for (var i in t.files)
            files.push( themeFile(path.pub, t, t.files[i], '.css') );

        return files;
    },

    lessFiles: function (themeName) {
        var lessFiles = {},
            t = theme[themeName],
            css, lss;

        for (var i in t.files) {
            css = themeFile(path.pub, t, t.files[i], '.css');
            lss = themeFile(path.pub, t, t.files[i], '.less');
            lessFiles[css] = lss;
        }

        return lessFiles;
    }
};

function themeFile(path, theme, file, ext) {
    return path +
        theme.area + '/' +
        theme.name + '/' +
        theme.locale + '/' +
        file + ext;
}
