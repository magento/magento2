/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require.config({
    paths: {
        'ko': 'ko/ko',
        'domReady': 'requirejs/domReady',
        'tests': 'dev/tests/js/spec'
    },
    shim: {
        'jquery/ui': ['jquery']
    }
});