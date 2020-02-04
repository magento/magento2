/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/loader'
], function ($) {
    'use strict';

    describe('mage/loader', function () {
        describe('Check loader', function () {
            var loaderSelector = '#loader';

            beforeEach(function () {
                var $loader = $('<div id="loader"/>');

                $('body').append($loader);
            });

            afterEach(function () {
                $(loaderSelector).remove();
                $(loaderSelector).loader('destroy');
            });

            it('Check that loader inited', function () {
                var $loader = $(loaderSelector).loader({
                    icon: 'icon.gif'
                });

                $loader.loader('show');

                expect($loader.is(':mage-loader')).toBe(true);
                expect($loader.find('p').text()).toBe('Please wait...');
                expect($loader.find('img').prop('src').split('/').pop()).toBe('icon.gif');
                expect($loader.find('img').prop('alt')).toBe('Loading...');
            });

            it('Body init', function () {
                var $loader = $('body').loader();

                $loader.loader('show');

                expect($loader.is(':mage-loader')).toBe(true);
                $loader.loader('destroy');
            });

            it('Check show/hide', function () {
                var $loader = $(loaderSelector).loader(),
                    $loadingMask;

                $loader.loader('show');
                $loadingMask = $('.loading-mask');
                expect($loadingMask.is(':visible')).toBe(true);

                $loader.loader('hide');
                expect($loadingMask.is(':hidden')).toBe(true);

                $loader.loader('show');
                $loader.trigger('processStop');
                expect($loadingMask.is(':hidden')).toBe(true);
            });

            it('Check destroy', function () {
                var $loader = $(loaderSelector).loader(),
                    $loadingMask;

                $loader.loader('show');
                $loadingMask = $('.loading-mask');
                expect($loadingMask.is(':visible')).toBe(true);

                $loader.loader('destroy');
                expect($loadingMask.is(':visible')).toBe(false);
            });
        });
    });
});
