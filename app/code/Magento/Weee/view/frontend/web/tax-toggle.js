/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery'
], function ($) {
    'use strict';

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
