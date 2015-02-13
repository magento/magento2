define(function () {
    'use strict';

    return {
        local: {
            path: 'text!tests/assets/text/local.html',
            result: '<span>Local Template</span>'
        },
        external: {
            path: 'text!tests/assets/text/external.html',
            result: '<span>External Template</span>'
        }
    };
});
