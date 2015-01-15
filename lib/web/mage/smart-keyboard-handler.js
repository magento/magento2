/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
],function($){
    'use strict';

    function KeyboardHandler() {
        var focusState = false;
        var tabFocusClass = 'keyfocus';
        return {
            apply: smartKeyboardFocus
        };

        /**
         * Tab key onKeypress handler. Apply main logic:
         *  - call differ actions onTabKeyPress and onClick
         */
        function smartKeyboardFocus() {
            $(document).on('keydown keypress', function(event){
                if(event.which === 9 && !focusState) {
                    $('body')
                        .on('focusin', onFocusInHandler)
                        .on('click', onClickHandler);
                }
            });

        }

        /**
         * Handle logic, when onTabKeyPress fired at first.
         * Then it changes state.
         */
        function onFocusInHandler () {
            focusState = true;
            $('body').addClass(tabFocusClass)
                    .off('focusin', onFocusInHandler);
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