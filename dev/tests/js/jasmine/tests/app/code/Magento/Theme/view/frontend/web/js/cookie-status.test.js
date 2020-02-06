
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'cookieStatus'
], function ($, Cookie) {
    'use strict';

    describe('Magento_Theme/js/cookie-status', function () {
        var widget,
            htmlContainer = '<div id="cookie-status" style="display: none"></div>',
            navigator;

        beforeEach(function () {
            $(document.body).append(htmlContainer);
            widget = new Cookie();
            navigator = window.navigator;
        });

        afterEach(function () {
            window.navigator = navigator;
        });

        it('verify initialization cookieStatus widget', function () {
            expect($.fn.cookieStatus).toBeDefined();
        });

        it('verify that modal does not shows when cookies are supported', function () {
            window.navigator = {
                cookieEnabled: true
            };
            widget._init();
            expect($(document.body).html()).not.toContain('<aside role="dialog" class="modal-popup');
        });

        it('shows the modal when cookies are not supported', function () {
            window.navigator = {
                cookieEnabled: false
            };
            widget._init();
            expect($(document.body).html()).toContain('<aside role="dialog" class="modal-popup');
        });

    });
});
