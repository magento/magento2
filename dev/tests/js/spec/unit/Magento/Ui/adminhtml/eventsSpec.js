/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/* global jasmine: true, expect: true, it: true, describe: true */
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
