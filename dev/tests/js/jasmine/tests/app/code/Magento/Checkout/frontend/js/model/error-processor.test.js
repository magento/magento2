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
                    build: jasmine.createSpy()
                },
                'consoleLogger': jasmine.createSpy('logger')
            },
            model,
            loggerModel;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Checkout/js/model/error-processor',
                'consoleLogger'
            ], function (processor, logger) {
                model = processor;
                loggerModel = logger;

                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        describe('Check process method', function () {
            it('check on success response with invalid response data', function () {
                var messageContainer = jasmine.createSpyObj('globalMessageList', ['addErrorMessage']),
                    messageObject = {
                        message: 'Something went wrong with your request. Please try again later.'
                    };

                spyOn(loggerModel, 'error');

                model.process({
                    status: 200,
                    responseText: ''
                }, messageContainer);

                expect(loggerModel.error).toHaveBeenCalled();

                expect(messageContainer.addErrorMessage)
                    .toHaveBeenCalledWith(messageObject);
            });
        });
    });
});
