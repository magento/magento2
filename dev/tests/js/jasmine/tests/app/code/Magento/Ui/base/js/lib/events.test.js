/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/core/events'
], function (EventBus) {
    'use strict';

    var EVENT = 'testEvent';

    describe('Magento_Ui/js/lib/core/events', function () {
        describe('"on" method', function () {
            afterEach(function () {
                EventBus.off(EVENT);
            });

            it('calls passed callback when event is triggered', function () {
                var callback = jasmine.createSpy();

                EventBus.on(EVENT, callback);

                EventBus.trigger(EVENT);

                expect(callback).toHaveBeenCalled();
            });

            it('calls callbacks in order they have been assigned', function () {
                var expected = '',
                    firstCallback,
                    secondCallback;

                /**
                 * Test callback
                 */
                firstCallback = function () {
                    expected += 'one';
                };

                /**
                 * Test callback
                 */
                secondCallback = function () {
                    expected += ' two';
                };

                EventBus.on(EVENT, firstCallback);
                EventBus.on(EVENT, secondCallback);

                EventBus.trigger(EVENT);

                expect(expected).toEqual('one two');
            });
        });
    });
});
