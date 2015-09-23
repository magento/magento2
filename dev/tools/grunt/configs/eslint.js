/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

module.exports = {
    file: {
        options: {
            configFile: 'dev/tests/static/testsuite/Magento/Test/Js/_files/eslint/.eslintrc',
            reset: true,
            useEslintrc: false
        },
        src: ''
    },
    test: {
        options: {
            configFile: 'dev/tests/static/testsuite/Magento/Test/Js/_files/eslint/.eslintrc',
            reset: true,
            outputFile: 'dev/tests/static/eslint-error-report.xml',
            format: 'junit',
            quiet: true
        },
        src: ''
    }
};
