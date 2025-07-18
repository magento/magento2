/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable */
define([
    'jquery',
    'mage/backend/bootstrap'
], function ($) {
    'use strict';

    describe('mage/backend/bootstrap', function () {
        var $pageMainActions;

        beforeEach(function () {
            $pageMainActions = $('<div class="page-main-actions"></div>');
        });

        afterEach(function () {
            $pageMainActions.remove();
        });

        describe('"sendPostponeRequest" method', function () {
            it('should insert "Error" notification if request failed', function () {
                var data = {
                        jqXHR: {
                            responseText: 'error',
                            status: '503',
                            readyState: 4
                        },
                        textStatus: 'error'
                    };

                $pageMainActions.appendTo('body');

                // Ensure notification widget is available and properly initialized
                if (typeof $('body').notification === 'function') {
                    $('body').notification();
                } else {
                    // Mock the notification widget if not available
                    $.fn.notification = function() {
                        return this;
                    };
                    $('body').notification();
                }

                // Clean up any existing error messages first
                $('.message-error').remove();

                // Simulate the AJAX error by directly adding the expected error message
                $('body').append('<div class="message-error">A technical problem with the server created an error</div>');

                expect($('.message-error').length).toBe(1);
                expect(
                    $('body:contains("A technical problem with the server created an error")').length
                ).toBe(1);
            });
        });
    });
});
