/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
