/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

module.exports = {
    firstLine: 'Copyright © <%= grunt.template.today("yyyy") %> Magento. All rights reserved.',
    secondLine: 'See COPYING.txt for license details.',

    css: function () {
        return '/**\n * ' + this.firstLine + '\n * ' + this.secondLine + '\n */\n';
    },

    less: function () {
        return '// /**\n//  * ' + this.firstLine + '\n//  * ' + this.secondLine + '\n//  */\n';
    },

    html: function () {
        return '<!--\n/**\n * ' + this.firstLine + '\n * ' + this.secondLine + '\n */\n-->\n';
    }
};
