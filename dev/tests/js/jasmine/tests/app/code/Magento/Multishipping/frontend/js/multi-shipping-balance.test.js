/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['squire', 'jquery'], function (Squire, $) {
    'use strict';

    var injector = new Squire(),
        dataPost = {
            postData: jasmine.createSpy()
        },
        mocks = {
            /** Stub */
            'mage/dataPost': function () {
                return dataPost;
            }
        },
        checkbox,
        url = 'example.com',

        /**
         * Toggle checkbox and assert that event was triggered.
         *
         * @param {Integer} value
         */
        toggleCheckbox = function (value) {
            checkbox.trigger('click');
            expect(dataPost.postData.calls.mostRecent().args[0]).toEqual({
                action: url,
                data: {
                    useBalance: value
                }
            });
        };

    beforeEach(function (done) {
        checkbox = $('<input type="checkbox" name="use_balance" checked="checked"/>');
        $(document.body).append(checkbox);

        injector.mock(mocks);
        injector.require(['multiShippingBalance'], function (balance) {
            balance({
                changeUrl: url
            }, checkbox);
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}

        checkbox.remove();
    });

    describe('multiShippingBalance', function () {
        it('Check actions after clicking on checkbox.', function () {
            toggleCheckbox(0);
            toggleCheckbox(1);
            toggleCheckbox(0);
            expect(dataPost.postData).toHaveBeenCalledTimes(3);
        });
    });
});
