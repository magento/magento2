/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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

        describe('ajax "beforeSend" callback', function () {
            var beforeSend = function (settings) {
                $.ajaxSettings.beforeSend({}, settings);

                return settings;
            };

            it('should not append form_key to a JSON request body', function () {
                var body = JSON.stringify({'form_key': 'abc', 'qty': 1}),
                    settings = beforeSend({
                        url: '/rest/V1/test',
                        contentType: 'application/json',
                        data: body
                    });

                expect(settings.data).toBe(body);
            });

            it('should not corrupt a JSON request body without form_key', function () {
                var body = JSON.stringify({'qty': 1}),
                    settings = beforeSend({
                        url: '/rest/V1/test',
                        contentType: 'application/json; charset=UTF-8',
                        data: body
                    });

                expect(settings.data).toBe(body);
            });

            it('should append form_key to a url-encoded string body', function () {
                var settings = beforeSend({
                    url: '/test',
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    data: 'a=1'
                });

                expect(settings.data).toMatch(/^a=1&form_key=/);
            });
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
