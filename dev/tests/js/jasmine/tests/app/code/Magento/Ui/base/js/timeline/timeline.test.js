/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Ui/js/timeline/timeline-view': jasmine.createSpy()
        },
        timeline;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Ui/js/timeline/timeline', 'knockoutjs/knockout-es5'], function (Constr) {
            timeline = new Constr({
                name: '',
                index: '',
                links: '',
                listens: '',
                viewConfig: {
                    component: '',
                    model: ''
                },
                dndConfig: {
                    enabled: false
                }
            });
            done();
        });
    });

    describe('Magento_Ui/js/timeline/timeline', function () {

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
