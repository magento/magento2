/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    var sections = {};

    return {
        get: function (actionName) {
            return sections[actionName];
        },
        sectionConfig: function(config) {
            sections = config
        }
    };
});
