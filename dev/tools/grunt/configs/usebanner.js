/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var banner = {
    firstLine: 'Copyright © 2015 Magento. All rights reserved.',
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

module.exports = {
    options: {
        position: 'top',
        linebreak: true
    },
    setup: {
        options: {
            banner: banner.css()
        },
        files: {
            src: '<%= path.css.setup %>/*.css'
        }
    },
    documentationCss: {
        options: {
            banner: banner.css()
        },
        files: {
            src: '<%= path.doc %>/**/*.css'
        }
    },
    documentationLess: {
        options: {
            banner: banner.less()
        },
        files: {
            src: '<%= path.doc %>/**/*.less'
        }
    },
    documentationHtml: {
        options: {
            banner: banner.html()
        },
        files: {
            src: '<%= path.doc %>/**/*.html'
        }
    }
};
