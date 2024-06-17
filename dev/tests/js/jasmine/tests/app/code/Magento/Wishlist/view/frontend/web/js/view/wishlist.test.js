/************************************************************************
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Wishlist/js/view/wishlist', function () {
        var wishlistComponent,
            mockWishlist,
            mockCustomerData,
            injector;

        function setupInjector() {
            injector = new Squire();

            mockWishlist = {
                counter: 1,
                items: [],
                websiteId: 1
            };
            injector.clean();

            mockCustomerData = {
                get: jasmine.createSpy('customerDataGet').and.returnValue(function () {
                    return mockWishlist;
                }),
                reload: jasmine.createSpy('customerDataReload'),
                invalidate: jasmine.createSpy(),
                getExpiredSectionNames: jasmine.createSpy('getExpiredSectionNames').and.returnValue([])
            };
            injector.mock('Magento_Customer/js/customer-data', mockCustomerData);
        }

        function cleanupInjector() {
            try {
                injector.clean();
                injector.remove();
                delete window.checkout;
            } catch (e) {}
        }

        async function loadWishlistComponent() {
            return new Promise(resolve => {
                injector.require(['Magento_Wishlist/js/view/wishlist'], async function (WishlistComponent) {
                    wishlistComponent = new WishlistComponent();
                    resolve();
                });
            });
        }

        beforeEach(async function () {
            setupInjector();
            await loadWishlistComponent();
        });

        afterEach(function () {
            cleanupInjector();
        });

        describe('Initialization', function () {
            it('should call customerData.get with "wishlist"', async function () {
                expect(mockCustomerData.get).toHaveBeenCalledWith('wishlist');
            });

            it('should invalidate wishlist if websiteIds do not match', async function () {
                window.checkout = { websiteId: 2 };
                await wishlistComponent.initialize();
                expect(mockCustomerData.invalidate).toHaveBeenCalledWith(['wishlist']);
            });

            it('should not reload wishlist if websiteIds match', async function () {
                window.checkout = { websiteId: 1 };
                await wishlistComponent.initialize();
                expect(mockCustomerData.reload).not.toHaveBeenCalled();
            });

            it('should reload wishlist if websiteIds do not match', async function () {
                window.checkout = { websiteId: 2 };
                await wishlistComponent.initialize();
                expect(mockCustomerData.reload).toHaveBeenCalledWith(['wishlist'], false);
            });
        });
    });
});
