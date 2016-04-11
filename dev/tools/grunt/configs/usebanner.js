/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

function printCopyright(lang) {
    var copyrightText = {
            firstLine: 'Copyright © 2016 Magento. All rights reserved.',
            secondLine: 'See COPYING.txt for license details.'
        },
        nlWin = '\r\n';
    switch (lang) {
        case 'css':
            return '/**' + nlWin + ' * ' + copyrightText.firstLine + nlWin + ' * ' + copyrightText.secondLine + nlWin + ' */' + nlWin;
            break;
        case 'less':
            return '// /**' + nlWin + '//  * ' + copyrightText.firstLine + nlWin + '//  * ' + copyrightText.secondLine + nlWin + '//  */' + nlWin;
            break;
        case 'html':
            return '<!--' + nlWin + '/**' + nlWin + ' * ' + copyrightText.firstLine + nlWin + ' * ' + copyrightText.secondLine + nlWin + ' */' + nlWin + '-->' + nlWin;
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
    updater: {
        options: {
            banner: printCopyright('css')
        },
        files: {
            src: '<%= path.css.updater %>/updater.css'
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
