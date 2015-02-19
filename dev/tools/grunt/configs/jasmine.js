/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var path = require('path');

module.exports = function (grunt) {
    var file = path.join(process.cwd(), 'dev/tests/js/framework/spec_runner'),
        specRunner = require(file)(grunt);

    return {
        options: {
            template: require('grunt-template-jasmine-requirejs'),
            ignoreEmpty: true
        },
        'lib-unit':               specRunner.configure('unit', 'lib', 8080),
        'lib-integration':        specRunner.configure('integration', 'lib', 8080),
        'backend-unit':           specRunner.configure('unit', 'adminhtml', 8000),
        'backend-integration':    specRunner.configure('integration', 'adminhtml', 8000),
        'frontend-unit':          specRunner.configure('unit', 'frontend', 3000),
        'frontend-integration':   specRunner.configure('integration', 'frontend', 3000)
    };
};
