/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable */
define([
    'mage/adminhtml/browser',
    'jquery'
], function (browser, $) {
    'use strict';

    var obj,
        originalJQueryAjax,
        openUrl = 'http://example.com/target_element_id/theTargetId/tree_path/wysiwyg&current_tree_path=d3lzaXd5Zw,';

    beforeEach(function () {
        // Store original $.ajax if it exists
        originalJQueryAjax = $.ajax;

        // Ensure $.ajax exists for testing
        if (!$.ajax) {
            $.ajax = function() {
                return {
                    done: function() { return this; },
                    fail: function() { return this; },
                    always: function() { return this; }
                };
            };
        }

        /**
         * Dummy constructor to use for instantiation
         * @constructor
         */
        var Constr = function () {};

        Constr.prototype = browser;

        obj = new Constr();
    });

    afterEach(function () {
        // Restore original $.ajax
        if (originalJQueryAjax) {
            $.ajax = originalJQueryAjax;
        } else {
            delete $.ajax;
        }
    });

    describe('"openDialog" method', function () {
        it('Opens dialog with provided targetElementId', function () {
            var options = {
                'targetElementId': 1
            };

            spyOn($, 'ajax').and.callFake(
                function () {
                    return {
                        /**
                         * Success result of ajax request
                         */
                        done: function () {
                            obj.targetElementId = 1;
                            obj.modalLoaded = true;
                        }
                    };
                });
            obj.openDialog(openUrl, 100, 100, 'title', options);
            obj.openDialog(openUrl, 100, 100, 'title', options);
            expect(obj.pathId).toBe('d3lzaXd5Zw,');
            expect($.ajax.calls.count()).toBe(1);
        });

        it('Opens dialog with provided url param', function () {
            spyOn($, 'ajax').and.callFake(
                function () {
                    return {
                        /**
                         * Success result of ajax request
                         */
                        done: function () {
                            obj.targetElementId = 'instance/url';
                            obj.modalLoaded = true;
                        }
                    };
                });
            obj.openDialog(openUrl, 100, 100, 'title', undefined);
            obj.openDialog(openUrl, 100, 100, 'title', undefined);
            expect($.ajax.calls.count()).toBe(1);
            expect(obj.pathId).toBe('d3lzaXd5Zw,');
        });
    });
});
