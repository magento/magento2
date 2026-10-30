/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

'use strict';

var nlWin = '\r\n',
    nlUnix = '\n';

function findCopyright(lang, nlSys) {
    var currentYear = new Date().getFullYear(),
        copyrightText = {
            firstLine: 'Copyright ' + currentYear + ' Adobe',
            secondLine: 'All Rights Reserved.'
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
