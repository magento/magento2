/**
 * Copyright © 2015 Magento. All rights reserved.
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
    css: {
        setup: 'setup/pub/styles'
    },
    less: {
        setup: 'setup/view/styles'
    },
    uglify: {
        legacy: 'lib/web/legacy-build.min.js'
    },
    doc: 'lib/web/css/docs',
    spec: 'dev/tests/js/spec'
};
