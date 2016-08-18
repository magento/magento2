/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo  = require('./combo'),
    themes = require('./themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        files: _.map(combo.cssFiles(name), function(f){
            return {src: f};
        })
    };
});

var postOptions = {
    options: {
        map: false,
        processors: [ 
            require('autoprefixer')({
                browsers: ['last 4 versions', 'ie 9']
            }),
            require('cssnano')({
                safe: true,
                autoprefixer: false,
                minifySelectors: false,
                reduceTransform: false
            })
        ]
    }
};

/**
 * Autoprefix and minify CSS
 */
module.exports = _.extend(themeOptions, postOptions);
