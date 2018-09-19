/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'prototype'
], function () {
    'use strict';

    /**
     * @param {*} selected
     * @param {Object} defaultsById
     */
    function parentThemeOnChange(selected, defaultsById) {
        var statusBar = $$('.tab-item-link')[0],
            isChanged = statusBar.hasClassName('changed'),
            defaults;

        if (!isChanged) {
            defaults = defaultsById[selected];
            $('theme_title').value = defaults['theme_title'];
        }
    }

    window.parentThemeOnChange = parentThemeOnChange;
});
