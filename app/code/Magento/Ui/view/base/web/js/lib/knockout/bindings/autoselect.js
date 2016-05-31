/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    '../template/renderer'
], function (ko, $, renderer) {
    'use strict';

    /**
     * 'Focus' event handler.
     *
     * @param {EventObject} e
     */
    function onFocus(e) {
        e.target.select();
    }

    ko.bindingHandlers.autoselect = {

        /**
         * Adds event handler which automatically
         * selects inputs' element text when field gets focused.
         */
        init: function (element, valueAccessor) {
            var enabled = ko.unwrap(valueAccessor());

            if (enabled !== false) {
                $(element).on('focus', onFocus);
            }
        }
    };

    renderer.addAttribute('autoselect');
});
