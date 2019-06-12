/**
 * Copyright © Magento, Inc. All rights reserved.
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
        mocks = {},
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/list/columns/image', 'knockoutjs/knockout-es5'],
            function (Constr) {
            obj = new Constr({
                sortable: true,
                sorting: false,
                headerTmpl: 'header',
                bodyTmpl: 'body',

                /** Stub */
                source: function () {
                    return {
                        column: {
                            image: 'image'
                        }
                    };
                }
            });
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Catalog/js/product/list/columns/image', function () {
        var image = {
            url: 'url',
            width: 100,
            height: 100,
            'resized_width': 200
        },
        code = 'code',
        row = {
            images: [
                {
                    label: 'first',
                    code: code
                },
                {
                    label: 'second',
                    code: code
                }
            ]
        };

        beforeEach(function () {
            obj.source = jasmine.createSpy().and.returnValue({
                column: {
                    image: {
                        imageCode: 'code'
                    }
                }
            });
        });
        describe('"getImages" method', function () {
            it('Check returned value', function () {
                obj.imageCode = code;
                expect(obj.getImage(row.images)).toBe(row.images[1]);
            });
        });
        describe('"getImageUrl" method', function () {
            it('Check call "getImage" method', function () {
                obj.getImage = jasmine.createSpy().and.returnValue(image);
                obj.getImageUrl(row);
                expect(obj.getImage).toHaveBeenCalledWith(row.images);
            });
        });
        describe('"getWidth" method', function () {
            it('Check call "getImage" method', function () {
                obj.getImage = jasmine.createSpy().and.returnValue(image);
                obj.getImageUrl(row);
                expect(obj.getImage).toHaveBeenCalledWith(row.images);
            });
        });
        describe('"getHeight" method', function () {
            it('Check call "getImage" method', function () {
                obj.getImage = jasmine.createSpy().and.returnValue(image);
                obj.getImageUrl(row);
                expect(obj.getImage).toHaveBeenCalledWith(row.images);
            });
        });
        describe('"getResizedImageWidth" method', function () {
            it('Check call "getImage" method', function () {
                obj.getImage = jasmine.createSpy().and.returnValue(image);
                obj.getImageUrl(row);
                expect(obj.getImage).toHaveBeenCalledWith(row.images);
            });
        });
    });
});
