/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/adminhtml/browser',
    'jquery'
], function (browser, $) {
    'use strict';

    var obj,
        openUrl = 'http://example.com/target_element_id/theTargetId/tree_path/wysiwyg&current_tree_path=d3lzaXd5Zw,';

    beforeEach(function () {
        /**
         * Dummy constructor to use for instantiation
         * @constructor
         */
        var Constr = function () {};

        Constr.prototype = browser;

        obj = new Constr();
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
