/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

require.config({
    baseUrl: './',
    bundles: {
        'mage/requirejs/static': [
            'buildTools',
            'jsbuild',
            'statistician',
            'text'
        ]
    },
    paths: {
        'tests': 'dev/tests/js/jasmine'
    },
    config: {
        jsbuild: {
            'dev/tests/js/jasmine/assets/jsbuild/local.js': 'define([], function () {\'use strict\'; return \'internal module\'; });'
        },
        text: {
            'dev/tests/js/jasmine/assets/text/local.html': '<!--\n/**\n * Copyright © 2016 Magento. All rights reserved.\n * See COPYING.txt for license details.\n */\n-->\n<span>Local Template</span>'
        }
    },
    deps: [
        'mage/requirejs/static'
    ]
});