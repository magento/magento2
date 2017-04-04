/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/timeline/timeline'
], function (Timeline) {
    'use strict';

    describe('Magento_Ui/js/timeline/timeline', function () {
        var timeline;

        beforeEach(function () {
            timeline = new Timeline({});
        });

        describe('isActive method', function () {
            it('record status is 1', function () {
                expect(timeline.isActive({
                    status: 1
                })).toBe(true);
            });

            it('record status is "1"', function () {
                expect(timeline.isActive({
                    status: '1'
                })).toBe(true);
            });

            it('record status is 2', function () {
                expect(timeline.isActive({
                    status: 2
                })).toBe(false);
            });
        });

        describe('isUpcoming method', function () {
            it('record status is 2', function () {
                expect(timeline.isUpcoming({
                    status: 2
                })).toBe(true);
            });

            it('record status is "2"', function () {
                expect(timeline.isUpcoming({
                    status: '2'
                })).toBe(true);
            });

            it('record status is 1', function () {
                expect(timeline.isUpcoming({
                    status: 1
                })).toBe(false);
            });
        });
    });
});
