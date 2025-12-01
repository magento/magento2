/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define(function () {
    'use strict';

    return {
        local: {
            path: 'text!tests/assets/text/local.html',
            result: '<!--\n/**\n * Copyright 2015 Adobe\n * All Rights Reserved.\n */\n-->\n<span>Local Template</span>'
        },
        external: {
            path: 'text!tests/assets/text/external.html',
            result: '<!--\n/**\n * Copyright 2015 Adobe\n * All Rights Reserved.\n */\n-->\n<span>External Template</span>'
        }
    };
});
