/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/events'
], function (EventBus) {
    'use strict';

    describe('EventBus', function () {
        describe('<on> method', function () {
            it('calls passed callback when event is triggered', function () {
                var callback = jasmine.createSpy('callback');

                EventBus.on('someEvent', callback);

                EventBus.trigger('someEvent');

                expect(callback).toHaveBeenCalled();
            });
        });
    });
});
