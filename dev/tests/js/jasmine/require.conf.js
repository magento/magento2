/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

require.config({
    bundles: {
        'mage/requirejs/static': [
            'buildTools',
            'jsbuild',
            'statistician',
            'text'
        ]
    },
    paths: {
        'dev/tests/js/jasmine': '../../../../../../dev/tests/js/jasmine',
        'tests': '../../../../../../dev/tests/js/jasmine',
        'squire': '../../../../../../node_modules/squirejs/src/Squire'
    },
    shim: {
        squire: {
            exports: 'squire'
        }
    },
    config: {
        jsbuild: {
            '../../../../../../dev/tests/js/jasmine/assets/jsbuild/local.js': 'define([], function () {\'use strict\'; return \'internal module\'; });'
        },
        text: {
            '../../../../../../dev/tests/js/jasmine/assets/text/local.html': '<!--\n/**\n * Copyright © Magento, Inc. All rights reserved.\n * See COPYING.txt for license details.\n */\n-->\n<span>Local Template</span>'
        }
    },
    deps: [
        'mage/requirejs/static'
    ]
});
