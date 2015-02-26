/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var banner = require('./banner');

module.exports = {
    options: {
        position: 'top',
        linebreak: true
    },
    setup: {
        options: {
            banner: banner.css()
        },
        files: {
            src: '<%= path.css.setup %>/*.css'
        }
    },
    documentationCss: {
        options: {
            banner: banner.css()
        },
        files: {
            src: '<%= path.doc %>/**/*.css'
        }
    },
    documentationLess: {
        options: {
            banner: banner.less()
        },
        files: {
            src: '<%= path.doc %>/**/*.less'
        }
    },
    documentationHtml: {
        options: {
            banner: banner.html()
        },
        files: {
            src: '<%= path.doc %>/**/*.html'
        }
    }
};
