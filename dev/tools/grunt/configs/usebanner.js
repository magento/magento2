/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

function printCopyright(lang) {
    var copyrightText = {
        firstLine: 'Copyright © 2015 Magento. All rights reserved.',
        secondLine: 'See COPYING.txt for license details.'
    };
    switch (lang) {
        case 'css':
            return '/**\n * ' + copyrightText.firstLine + '\n * ' + copyrightText.secondLine + '\n */\n';
            break;
        case 'less':
            return '// /**\n//  * ' + copyrightText.firstLine + '\n//  * ' + copyrightText.secondLine + '\n//  */\n';
            break;
        case 'html':
            return '<!--\n/**\n * ' + copyrightText.firstLine + '\n * ' + copyrightText.secondLine + '\n */\n-->\n';
            break;
        default:
            return;
    }
}

module.exports = {
    options: {
        position: 'top',
        linebreak: true
    },
    setup: {
        options: {
            banner: printCopyright('css')
        },
        files: {
            src: '<%= path.css.setup %>/*.css'
        }
    },
    documentationCss: {
        options: {
            banner: printCopyright('css')
        },
        files: {
            src: '<%= path.doc %>/**/*.css'
        }
    },
    documentationLess: {
        options: {
            banner: printCopyright('less')
        },
        files: {
            src: '<%= path.doc %>/**/*.less'
        }
    },
    documentationHtml: {
        options: {
            banner: printCopyright('html')
        },
        files: {
            src: '<%= path.doc %>/**/*.html'
        }
    }
};
