/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo = require('./combo');

/**
 * Compiles Less to CSS and generates necessary files if requested.
 */
module.exports = {
    options: {
        sourceMap: true,
        strictImports: false,
        sourceMapRootpath: '/',
        dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
        ieCompat: false
    },
    backend: {
        files: combo.lessFiles('backend')
    },
    blank: {
        files: combo.lessFiles('blank')
    },
    luma: {
        files: combo.lessFiles('luma')
    },
    setup: {
        files: {
            '<%= path.css.setup %>/setup.css': '<%= path.less.setup %>/setup.less'
        }
    },
    documentation: {
        files: {
            '<%= path.doc %>/docs.css': '<%= path.doc %>/source/docs.less'
        }
    }
};
