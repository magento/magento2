/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var svgo = require('imagemin-svgo');

/**
 * Images optimization.
 */
module.exports = {
    png: {
        options: {
            optimizationLevel: 7
        },
        files: [{
            expand: true,
            src: ['**/*.png'],
            ext: '.png'
        }]
    },
    jpg: {
        options: {
            progressive: true
        },
        files: [{
            expand: true,
            src: ['**/*.jpg'],
            ext: '.jpg'
        }]
    },
    gif: {
        files: [{
            expand: true,
            src: ['**/*.gif'],
            ext: '.gif'
        }]
    },
    svg: {
        options: {
            use: [svgo()]
        },
        files: [{
            expand: true,
            src: ['**/*.svg'],
            ext: '.svg'
        }]
    }
};
