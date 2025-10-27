/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
define([
    'jquery',
    'Magento_ProductVideo/js/fotorama-add-video-events'
], function ($) {
    'use strict';

    describe('Magento_ProductVideo/js/fotorama-add-video-events.js', function () {
        let element;

        beforeEach(function () {
            element = document.createElement('div');
            document.body.appendChild(element);
        });

        afterEach(function () {
            document.body.removeChild(element);
            element = null;
        });

        describe('_create', function () {
            let _initialize, _defaultVideoData;

            beforeEach(function () {
                _initialize = $.mage.AddFotoramaVideoEvents.prototype._initialize;
                _defaultVideoData = $.mage.AddFotoramaVideoEvents.prototype.defaultVideoData;
            });

            afterEach(function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = _initialize;
                $.mage.AddFotoramaVideoEvents.prototype.defaultVideoData = _defaultVideoData;
            });
            it('Should set defaultVideoData to videoData if defaultVideoData is empty', function () {
                let videoData = [{}, {}];

                $(element).AddFotoramaVideoEvents({videoData});
                expect($(element).AddFotoramaVideoEvents('instance').defaultVideoData).toEqual(videoData);
            });
            it('Should set defaultVideoData and videoData to videoDataPlaceholder if they are empty',
                function () {
                    $(element).AddFotoramaVideoEvents();
                    expect($(element).AddFotoramaVideoEvents('instance').defaultVideoData).toEqual(
                        $.mage.AddFotoramaVideoEvents.prototype.videoDataPlaceholder
                    );
                    expect($(element).AddFotoramaVideoEvents('instance').options.videoData).toEqual(
                        $.mage.AddFotoramaVideoEvents.prototype.videoDataPlaceholder
                    );
                });
            it('Should not set defaultVideoData to videoData if defaultVideoData is not empty', function () {
                let videoData = [{}, {}], defaultVideoData = [{}];

                $.mage.AddFotoramaVideoEvents.prototype.defaultVideoData = defaultVideoData;
                $(element).AddFotoramaVideoEvents({videoData, defaultVideoData});
                expect($(element).AddFotoramaVideoEvents('instance').defaultVideoData).toEqual(defaultVideoData);
            });
            it('Should call _initialize immediately if gallery is already loaded', function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = jasmine.createSpy();
                $(element).data('gallery', true);
                $(element).AddFotoramaVideoEvents();
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).toHaveBeenCalled();
            });
            it('Should call _initialize only after gallery is loaded', function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = jasmine.createSpy();
                $(element).AddFotoramaVideoEvents();
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).not.toHaveBeenCalled();
                $(element).trigger('gallery:loaded');
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).toHaveBeenCalled();
            });
        });
        describe('_setOptions', function () {
            let _initialize;

            beforeEach(function () {
                _initialize = $.mage.AddFotoramaVideoEvents.prototype._initialize;
            });

            afterEach(function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = _initialize;
            });
            it('Should call _initialize immediately if gallery is already loaded', function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = jasmine.createSpy();
                // initialize
                $(element).AddFotoramaVideoEvents();
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).not.toHaveBeenCalled();
                $(element).data('gallery', true);
                $(element).trigger('gallery:loaded');
                // set options after gallery is loaded
                $(element).AddFotoramaVideoEvents({videoData: []});
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).toHaveBeenCalledTimes(2);
            });
            it('Should call _initialize only after gallery is loaded', function () {
                $.mage.AddFotoramaVideoEvents.prototype._initialize = jasmine.createSpy();
                // initialize
                $(element).AddFotoramaVideoEvents();
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).not.toHaveBeenCalled();
                // set options after gallery is loaded
                $(element).AddFotoramaVideoEvents({videoData: []});
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).not.toHaveBeenCalled();
                $(element).data('gallery', true);
                $(element).trigger('gallery:loaded');
                expect($.mage.AddFotoramaVideoEvents.prototype._initialize).toHaveBeenCalledTimes(2);
            });
        });
    });
});
