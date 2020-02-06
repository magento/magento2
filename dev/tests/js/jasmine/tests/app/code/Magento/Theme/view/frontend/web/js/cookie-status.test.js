
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

        it('verify cookie-status initialization', function () {
            expect($.fn.cookieStatus).toBeDefined();
            window.navigator = {
                cookieEnabled: false
            };
            widget._init();
            expect($('.cookie-status').length).toBe(1);
            expect($(document.body).html()).toContain('<aside role="dialog" class="modal-popup');
        });

    });
});
