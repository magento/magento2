/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/* global jQuery */
/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire'
], function ($, Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Ui/js/modal/alert': jasmine.createSpy(),
            'uiRegistry': jasmine.createSpy()
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Analytics/js/modal/modal-component'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                links: '',
                listens: '',
                form: function () {
                    return {
                        source: {
                            data: {}
                        }
                    };
                }
            });
            done();
        });
    });

    describe('Magento_Analytics/js/modal/modal-component', function () {
        describe('"sendPostponeRequest" method', function () {
            it('should send a ajax request', function () {
                spyOn(jQuery, 'ajax').and.callFake(function () {
                    var d = $.Deferred();

                    d.resolve({
                        'success': true
                    });

                    return d.promise();
                });

                obj.sendPostponeRequest({});

                expect(jQuery.ajax).toHaveBeenCalled();
            });

            it('should call "onError" method if ajax received error', function () {
                spyOn(obj, 'onError');
                spyOn(jQuery, 'ajax').and.callFake(function () {
                    var d = $.Deferred();

                    d.resolve({
                        'error': true
                    });

                    return d.promise();
                });

                obj.sendPostponeRequest({});

                expect(jQuery.ajax).toHaveBeenCalled();
                expect(obj.onError).toHaveBeenCalled();
            });

            it('should call "onError" method if request failed', function () {
                spyOn(obj, 'onError');
                spyOn(jQuery, 'ajax').and.callFake(function () {
                    var d = $.Deferred();

                    d.reject();

                    return d.promise();
                });

                obj.sendPostponeRequest({});

                expect(jQuery.ajax).toHaveBeenCalled();
                expect(obj.onError).toHaveBeenCalled();
            });
        });

        describe('"onError" method', function () {
            var abortRequest = {
                    statusText: 'abort'
                },
                errorRequest = {
                    error: true,
                    message: 'Error text'
                };

            it('should do nothing if request aborted', function () {
                expect(obj.onError(abortRequest)).toBeUndefined();
            });

            it('should show alert with error', function () {
                obj.onError(errorRequest);
                expect(mocks['Magento_Ui/js/modal/alert']).toHaveBeenCalled();
            });
        });

        describe('"actionCancel" method', function () {
            it('should call "sendPostponeRequest" and "closeModal" methods', function () {
                spyOn(obj, 'sendPostponeRequest');
                spyOn(obj, 'closeModal');
                obj.actionCancel();
                expect(obj.sendPostponeRequest).toHaveBeenCalledWith(obj.postponeOptions);
                expect(obj.closeModal).toHaveBeenCalled();
            });
        });
    });
});
