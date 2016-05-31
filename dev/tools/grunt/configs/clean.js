/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var themes = require('./themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.tmp %>/cache/**/*",
                    "<%= combo.autopath(\""+name+"\", path.pub ) %>**/*",
                    "<%= combo.autopath(\""+name+"\", path.tmpLess) %>**/*",
                    "<%= combo.autopath(\""+name+"\", path.tmpSource) %>**/*"
                ]
            }
        ]
    };
});

var cleanOptions = {
    "var": {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.tmp %>/cache/**/*",
                    "<%= path.tmp %>/generation/**/*",
                    "<%= path.tmp %>/log/**/*",
                    "<%= path.tmp %>/maps/**/*",
                    "<%= path.tmp %>/page_cache/**/*",
                    "<%= path.tmp %>/tmp/**/*",
                    "<%= path.tmp %>/view/**/*",
                    "<%= path.tmp %>/view_preprocessed/**/*"
                ]
            }
        ]
    },
    "pub": {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.pub %>frontend/**/*",
                    "<%= path.pub %>adminhtml/**/*"
                ]
            }
        ]
    },
    "styles": {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.tmp %>/view_preprocessed/**/*",
                    "<%= path.tmp %>/cache/**/*",
                    "<%= path.pub %>frontend/**/*.less",
                    "<%= path.pub %>frontend/**/*.css",
                    "<%= path.pub %>adminhtml/**/*.less",
                    "<%= path.pub %>adminhtml/**/*.css"
                ]
            }
        ]
    },
    "markup": {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.tmp %>/cache/**/*",
                    "<%= path.tmp %>/generation/**/*",
                    "<%= path.tmp %>/view_preprocessed/html/**/*",
                    "<%= path.tmp %>/page_cache/**/*"
                ]
            }
        ]
    },
    "js": {
        "force": true,
        "files": [
            {
                "force": true,
                "dot": true,
                "src": [
                    "<%= path.pub %>**/*.js",
                    "<%= path.pub %>**/*.html",
                    "<%= path.pub %>_requirejs/**/*"
                ]
            }
        ]
    }
};

module.exports = _.extend(cleanOptions, themeOptions);

