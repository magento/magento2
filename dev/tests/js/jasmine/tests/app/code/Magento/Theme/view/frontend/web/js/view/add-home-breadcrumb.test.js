/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks, no-undef */
define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Theme/js/model/breadcrumbs': jasmine.createSpy('breadcrumb'),
            'Magento_Theme/js/model/breadcrumb-list': jasmine.createSpyObj(['unshift'])
        };

    describe('Magento_Theme/js/view/breadcrumbs', function () {
        var breadcrumbs,
            mixin,
            defaultContext = require.s.contexts._;

        beforeEach(function (done) {
            window.BASE_URL = window.location.hostname;

            injector.mock(mocks);
            injector.require(
                [
                    'Magento_Theme/js/view/add-home-breadcrumb',
                    'Magento_Theme/js/model/breadcrumbs'
                ], function (homeMixin, breadcrumbWidget) {
                    mixin = homeMixin;
                    breadcrumbs = breadcrumbWidget;
                    done();
                }
            );
        });

        afterEach(function () {
            delete window.BASE_URL;
        });

        it('mixin is applied to Magento_Theme/js/view/breadcrumbs', function () {
            var breadcrumbsMixins = defaultContext.config.config.mixins['Magento_Theme/js/view/breadcrumbs'];

            expect(breadcrumbsMixins['Magento_Theme/js/view/add-home-breadcrumb']).toBe(true);
        });

        it('Magento_Theme/js/model/breadcrumb-list is populated with "Home Page" crumb', function () {

            mixin(breadcrumbs);

            expect(mocks['Magento_Theme/js/model/breadcrumb-list'].unshift).toHaveBeenCalledWith(
                jasmine.objectContaining(
                    {
                        name: 'home',
                        link: window.BASE_URL
                    }
                )
            );
        });
    });
});
