/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var nlWin = '\r\n',
    nlUnix = '\n';

function findCopyright(lang, nlSys) {
    var copyrightText = {
        firstLine: 'Copyright © 2013-2017 Magento, Inc. All rights reserved.',
        secondLine: 'See COPYING.txt for license details.'
    };
    switch (lang) {
        case 'less':
            return new RegExp(
                '// /\\*\\*' + nlSys + '//  \\* ' +
                copyrightText.firstLine +
                '' + nlSys + '//  \\* ' +
                copyrightText.secondLine +
                '' + nlSys + '//  \\*/' + nlSys + nlSys
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
                    match: findCopyright('less', nlWin),
                    replacement: ''
                },
                {
                    match: findCopyright('less', nlUnix),
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
