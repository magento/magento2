/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var theme = require('./themes'),
    path = require('./path');

/**
 * Define Combos for repetitive code.
 */
module.exports = {
    collector: function (themeName) {
        var cmdPlus = /^win/.test(process.platform) ? ' & ' : ' && ',
            command = 'grunt --force clean:' + themeName + cmdPlus;

        command = command + 'php -f dev/tools/Magento/Tools/Webdev/file_assembler.php --' +
        ' --locale=' + theme[themeName].locale +
        ' --area=' + theme[themeName].area +
        ' --theme=' + theme[themeName].name +
        ' --files=' + theme[themeName].files.join(',') +
        ' --ext=' + theme[themeName].dsl;

        return command;
    },

    autopath: function (themeName) {
        return path.pub +
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
