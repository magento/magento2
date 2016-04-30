/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
],function($){
    'use strict';

    function KeyboardHandler() {
        var body = $('body'),
            focusState = false,
            tabFocusClass = '_keyfocus',
            productsGrid = '[data-container="product-grid"]',
            catalogProductsGrid = $(productsGrid),
            CODE_TAB = 9;

        return {
            apply: smartKeyboardFocus
        };

        /**
         * Tab key onKeypress handler. Apply main logic:
         *  - call differ actions onTabKeyPress and onClick
         */
        function smartKeyboardFocus() {
            $(document).on('keydown keypress', function(event) {
                if (event.which === CODE_TAB && !focusState) {
                    body
                        .on('focusin.keyboardHandler', onFocusInHandler)
                        .on('click', onClickHandler);
                }
            });

            // ARIA support for catalog grid products
            if (catalogProductsGrid.length) {
                body.on('focusin.gridProducts', productsGrid, function() {
                    if (body.hasClass(tabFocusClass)) {
                        $(this).addClass('active');
                    }
                });
                body.on('focusout.gridProducts', productsGrid, function() {
                    $(this).removeClass('active');
                });
            }
        }

        /**
         * Handle logic, when onTabKeyPress fired at first.
         * Then it changes state.
         */
        function onFocusInHandler () {
            focusState = true;
            $('body').addClass(tabFocusClass)
                .off('focusin.keyboardHandler', onFocusInHandler);
        }

        /**
         * Handle logic to remove state after onTabKeyPress to normal.
         * @param {Event} event
         */
        function onClickHandler(event) {
            focusState  = false;
            $('body').removeClass(tabFocusClass)
                .off('click', onClickHandler);
        }
    }

    return new KeyboardHandler;
});