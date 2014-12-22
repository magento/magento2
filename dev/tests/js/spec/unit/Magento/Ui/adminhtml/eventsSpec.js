/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'Magento_Ui/js/lib/events'
], function (EventBus) {
    'use strict';

    describe('EventBus', function () {
        it('has <on> method', function () {
            expect('on' in EventBus).toBe(true);
        });
    });
});