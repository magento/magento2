/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {Object} config
     * @param {jQuery.Event} e
     */
    function onToggle(config, e) {
        var elem = $(e.currentTarget),
            expandedClassName = config.expandedClassName || 'cart-tax-total-expanded';

        elem.toggleClass(expandedClassName);

        $(config.itemTaxId).toggle();
    }

    return function (data, el) {
        $(el).on('click', onToggle.bind(null, data));
    };
});
