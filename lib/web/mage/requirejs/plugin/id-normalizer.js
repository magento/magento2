/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint globalstrict: true*/
/*global define: false*/
"use strict";

/**
 * This is simple normalization plugin for RequireJS, which converts Magento modular references to normalized paths.
 * E.g. 'Magento_Catalog::js/scripts.js' -> 'Magento_Catalog/js/scripts.js'.
 */
define({
    /**
     * Normalize Magento modular ID
     */
    normalize: function (name, normalize) {
        return name.replace('::', '/');
    },

    /**
     * load() is not needed for this plugin, but is required by RequireJS.
     * So it is just proxy over default implementation.
     */
    load: function (name, parentRequire, onload, config) {
        parentRequire([name], onload);
    }
});
