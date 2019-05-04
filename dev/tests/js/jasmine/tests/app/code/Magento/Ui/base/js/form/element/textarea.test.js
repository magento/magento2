/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/textarea', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Ui/js/lib/registry/registry': {
                    /** Method stub. */
                    get: function () {
                        return {
                            get: jasmine.createSpy(),
                            set: jasmine.createSpy()
                        };
                    },
                    options: jasmine.createSpy(),
                    create: jasmine.createSpy(),
                    set: jasmine.createSpy(),
                    async: jasmine.createSpy()
                },
                '/mage/utils/wrapper': jasmine.createSpy()
            },
            model,
            dataScope = 'dataScope';

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/textarea',
                'knockoutjs/knockout-es5'
            ], function (Constr) {
                model = new Constr({
                    provider: 'provName',
                    name: '',
                    index: '',
                    dataScope: dataScope
                });

                done();
            });
        });

        it('check if component defined', function () {
            expect(model).toBeDefined();
        });
    });
});
