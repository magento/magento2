/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

(function () {
    'use strict';

    var imageminSvgo = require('imagemin-svgo'),
        svgo = imageminSvgo.default || imageminSvgo;

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
})();
