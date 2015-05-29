/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var nlWin = '\r\n',
    nlMac = '\n';

function findCopyright(lang, newLineSystem) {
    var copyrightText = {
        firstLine: 'Copyright © 2015 Magento. All rights reserved.',
        secondLine: 'See COPYING.txt for license details.'
    };
    switch (lang) {
        case 'less':
            return new RegExp(
                '// /\\*\\*' + newLineSystem + '//  \\* ' +
                copyrightText.firstLine +
                '' + newLineSystem + '//  \\* ' +
                copyrightText.secondLine +
                '' + newLineSystem + '//  \\*/' + newLineSystem + newLineSystem
            );
            break;
        default:
            return;
    }
}

module.exports = {
    documentation: {
        options: {
            patterns: [
                {
                    match: findCopyright('less', nlMac),
                    replacement: ''
                },
                {
                    match: findCopyright('less', nlWin),
                    replacement: ''
                }
            ]
        },
        files: [{
            expand: true,
            flatten: true,
            src: [
                '<%= path.doc %>/source/**/*.less'
            ],
            dest: '<%= path.doc %>/source/'
        }]
    }

};
