/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require.config({
    bundles: {
        'mage/requirejs/static': [
            'jsbuild',
            'text',
            'buildTools'
        ]
    },
    config: {
        jsbuild: {
            'dev/tests/js/spec/assets/jsbuild/local.js': 'define([], function () {\'use strict\'; return \'internal module\'; });'
        },
        text: {
            'dev/tests/js/spec/assets/text/local.html': '<span>Local Template</span>'
        }
    },
    deps: [
        'mage/requirejs/static'
    ],
    paths: {
        'jquery/ui': 'jquery/jquery-ui'
    }
});
