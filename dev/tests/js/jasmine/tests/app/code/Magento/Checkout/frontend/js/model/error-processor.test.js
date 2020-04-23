/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Checkout/js/model/error-processor', function () {
        var injector = new Squire(),
            mocks = {
                'mage/url': {
                    /** Method stub. */
                    build: jasmine.createSpy()
                },
                'Magento_Ui/js/model/messageList': jasmine.createSpy('globalList')
            },
            model;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Checkout/js/model/error-processor'
            ], function (processor) {
                model = processor;

                done();
            });
        });

        describe('Check process method', function () {
            it('check on success response with valid response data', function () {
                var messageObject = {
                        message: 'Valid error message!'
                    },
                    messageContainer = jasmine.createSpyObj('globalMessageList', ['addErrorMessage']);

                model.process({
                    status: 200,
                    responseText: JSON.stringify(messageObject)
                }, messageContainer);
                expect(messageContainer.addErrorMessage).toHaveBeenCalledWith(messageObject);
            });

            it('check on success response with invalid response data', function () {
                var messageContainer = jasmine.createSpyObj('globalMessageList', ['addErrorMessage']),
                    messageObject = {
                        message: 'Something went wrong with your request. Please try again later.'
                    };

                model.process({
                    status: 200,
                    responseText: ''
                }, messageContainer);
                expect(messageContainer.addErrorMessage)
                    .toHaveBeenCalledWith(messageObject);
            });

            it('check on failed status', function () {
                var messageContainer = jasmine.createSpyObj('globalMessageList', ['addErrorMessage']);

                spyOn(window.location, 'replace').and.callFake(function () {});
                model.process({
                    status: 401,
                    responseText: ''
                }, messageContainer);
                expect(mocks['mage/url'].build)
                    .toHaveBeenCalled();
            });
        });
    });
});
