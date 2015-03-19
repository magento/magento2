/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

require.config({
    paths: {
        'jquery/ui': 'jquery/jquery-ui-1.9.2',
        'ko': 'ko/ko'
    },
    shim: {
        'jquery/ui': ['jquery']
    },
    bundles: {
        'mage/requirejs/static': [
            'buildTools'
        ]
    },
    deps: [
        'mage/requirejs/static'
    ]
});