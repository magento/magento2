/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

/**
 * Replace task for backend migration
 */
module.exports = {
    escapeCalc: {
        src: ['<%= combo.autopath("backend","pub") %>/css/styles.css'], // source files array (supports minimatch)
        dest: '<%= combo.autopath("backend","pub") %>/css/override.less', // destination directory or file
        replacements: [{
            from: /:(.*calc.*);/g, // regex replacement ('Fooo' to 'Mooo')
            to: ': ~"$1";'
        }, {
            from: /\/\*# sourc.*/g, // regex replacement ('Fooo' to 'Mooo')
            to: ''
        }]
    }
};
