/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
