/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire',
    'text!tests/assets/lib/web/mage/account_menu.html'
], function ($, Squire, header) {
    'use strict';

    describe('Magento_Theme/js/theme', function () {
        var injector = new Squire(),
            mocks = {};

        beforeEach(function (done) {
            var $menu = $(header);

            $('body').append($menu);

            injector.mock(mocks);
            injector.require(['Magento_Theme/js/theme'], function () {
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
                header = null;
            } catch (e) {}
        });

        it('should add suffix "_mobile" to the html ID attribute (if exists) ' +
            'for every menu link to make IDs unique after cloning', function () {
            var suffix = '_mobile',
                menuItems = [
                    {
                        id: '#my-account' + suffix,
                        link: 'http://magento.store/account/'
                    },
                    {
                        id: '#my-wishlist' + suffix,
                        link: 'http://magento.store/wishlist/'
                    },
                    {
                        id: '#my-orders' + suffix,
                        link: 'http://magento.store/orders/'
                    },
                    {
                        id: '#my-addresses' + suffix,
                        link: 'http://magento.store/addresses/'
                    },
                    {
                        id: '#create-account' + suffix,
                        link: 'http://magento.store/customer/account/create/'
                    }
                ];

            menuItems.forEach(function (item) {
                expect($(item.id).attr('href')).toBe(item.link);
            });
        });
    });
});
