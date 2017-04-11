/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (DynamicRows) {
    'use strict';

    var model;

    beforeEach(function(done) {
        model = new DynamicRows({});
        done();
    });

    describe('Magento_Ui/js/dynamic-rows/dynamic-rows', function () {
        it('changePage without Records', function() {
            model.recordData = function () {
                return {
                    length: 0
                };
            };

            expect(model.changePage(1)).toBeFalsy();
        });

        it('changePage with Fake Page', function() {
            model.pages = function () {
                return 3;
            };

            expect(model.changePage(4)).toBeFalsy();
        });

        it('changePage', function() {
            model.startIndex = 0;
            model.pageSize = 3;
            model.relatedData = [
                {"a": "b"},
                {"b": "c"},
                {"v": "g"}
            ];

            model.pages = function () {
                return 3;
            };
            model.changePage(2);

            expect(model.templates.record.recordId).toBe(2);//last record number is 3
        });
    });
});
