/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'Magento_Ui/js/grid/dnd'
], function (ko, $, Dnd) {
    'use strict';

    ko.bindingHandlers.gridDnd = {
        init: function (element, valueAccessor, allBindings, viewModel) {
            var callback = valueAccessor();

            setTimeout(function () {
                new Dnd({
                    grid: element,
                    dragGridSelector: '.data-grid._dragging-copy',
                    columnsSelector: 'thead th._draggable'
                });
            }, 2000);
        }
    };
});
