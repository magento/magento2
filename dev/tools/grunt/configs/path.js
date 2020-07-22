/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

/**
 * Define paths.
 */
module.exports = {
    pub: 'pub/static/',
    tmpLess: 'var/view_preprocessed/less/',
    tmpSource: 'var/view_preprocessed/source/',
    tmp: 'var',
    deployedVersion: 'pub/static/deployed_version.txt',
    css: {
        setup: 'setup/pub/styles',
        updater: '../magento2-updater/pub/css'
    },
    less: {
        setup: 'app/design/adminhtml/Magento/backend/web/app/setup/styles/less',
        updater: 'app/design/adminhtml/Magento/backend/web/app/updater/styles/less'
    },
    uglify: {
        legacy: 'lib/web/legacy-build.min.js'
    },
    doc: 'lib/web/css/docs',
    spec: 'dev/tests/js/spec',
    static: {
        dir: 'dev/tests/static/testsuite/Magento/Test/Js/_files',
        whitelist: 'dev/tests/static/testsuite/Magento/Test/Js/_files/whitelist/',
        blacklist: 'dev/tests/static/testsuite/Magento/Test/Js/_files/blacklist/',
        tmp: 'validation-files.txt'
    },
    generation: 'generated/'
};
