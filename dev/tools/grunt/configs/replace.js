/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

function findCopyright(lang) {
    var copyrightText = {
        firstLine: 'Copyright © 2015 Magento. All rights reserved.',
        secondLine: 'See COPYING.txt for license details.'
    };
    switch (lang) {
        case 'less':
            return new RegExp(
                '// /\\*\\*\r\n//  \\* ' +
                copyrightText.firstLine +
                '\r\n//  \\* ' +
                copyrightText.secondLine +
                '\r\n//  \\*/\r\n\r\n'
            );
            break;
        default:
            return;
    }
}

module.exports = {
    documentation: {
        options: {
            patterns: [{
                match: findCopyright('less'),
                replacement: ''
            }]
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
