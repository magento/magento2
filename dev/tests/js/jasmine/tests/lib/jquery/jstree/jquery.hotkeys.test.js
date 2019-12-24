/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/jstree/jquery.hotkeys'
], function ($) {
    'use strict';

    describe('Test for jquery/jstree/jquery.hotkeys', function () {
        var divElement = $('<div></div>'),
            divBodyAfterTrigger = 'pressed',
            inputNumberElement = $('<input type="number">');

        beforeAll(function () {
            /**
             * Insert text to the divElement
             */
            var addHtmlToDivElement = function () {
                divElement.html(divBodyAfterTrigger);
            };

            $(document).bind('keyup', 'right', addHtmlToDivElement);
            $(document).bind('keyup', 'left', addHtmlToDivElement);

        });

        beforeEach(function () {
            inputNumberElement.appendTo(document.body);
            divElement.appendTo(document.body);
        });

        afterEach(function () {
            divElement.remove();
            inputNumberElement.remove();
        });

        it('Check "left key" hotkey is not being processed when number input is focused', function () {
            var keypress = $.Event('keyup');

            keypress.which = 37; // "left arrow" key
            inputNumberElement.trigger(keypress);

            expect(divElement.html()).toEqual('');
        });

        it('Check "right key" hotkey is not being processed when number input is focused', function () {
            var keypress = $.Event('keyup');

            keypress.which = 39; // "right arrow" key
            inputNumberElement.trigger(keypress);

            expect(divElement.html()).toEqual('');
        });

        it('Check "left key" hotkey is being processed when registered on the page', function () {
            var keypress = $.Event('keyup');

            keypress.which = 37; // "left arrow" key
            divElement.trigger(keypress);

            expect(divElement.html()).toEqual(divBodyAfterTrigger);
        });

        it('Check "right key" hotkey is being processed when registered on the page', function () {
            var keypress = $.Event('keyup');

            keypress.which = 39; // "right arrow" key
            $('body').trigger(keypress);

            expect(divElement.html()).toEqual(divBodyAfterTrigger);
        });

    });
});
