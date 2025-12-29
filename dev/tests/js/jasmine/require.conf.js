/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
            '../../../../../../dev/tests/js/jasmine/assets/text/local.html': '<!--\n/**\n * Copyright 2015 Adobe\n * All Rights Reserved.\n */\n-->\n<span>Local Template</span>'
        }
    },
    deps: [
        'mage/requirejs/static'
    ]
});
