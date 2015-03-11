/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

/**
 * Styles autoprefixer
 */
module.exports = {
    options: {
        browsers: [
            'last 2 versions',
            'ie 9'
        ]
    },
    setup: {
        src: '<%= path.css.setup %>/setup.css'
    }
};
