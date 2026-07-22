/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Wishlist/js/view/wishlist', function () {
        var wishlistComponent,
            mockWishlist,
            mockCompany,
            mockCustomerData,
            injector;

        function setupInjector() {
            injector = new Squire();

            mockWishlist = {
                counter: 1,
                items: [{ id: 1, name: 'Test Product' }],
                storeId: 1
            };

            mockCompany = {
                is_enabled: true
            };

            injector.clean();

            mockCustomerData = {
                get: jasmine.createSpy('customerDataGet').and.callFake(function (key) {
                    if (key === 'wishlist') {
                        return function () { return mockWishlist; };
                    } else if (key === 'company') {
                        return function () { return mockCompany; };
                    }
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

            it('should call customerData.get with "company"', async function () {
                expect(mockCustomerData.get).toHaveBeenCalledWith('company');
            });

            it('should invalidate wishlist if storeIds do not match', async function () {
                window.checkout = { storeId: 2 };
                await wishlistComponent.initialize();
                expect(mockCustomerData.invalidate).toHaveBeenCalledWith(['wishlist']);
            });

            it('should not reload wishlist if storeIds match and company is disabled', async function () {
                window.checkout = { storeId: 1 };
                mockCompany.is_enabled = false;
                await wishlistComponent.initialize();
                expect(mockCustomerData.reload).not.toHaveBeenCalledWith(['wishlist'], false);
            });

            it('should reload wishlist if storeIds do not match', async function () {
                window.checkout = { storeId: 2 };
                await wishlistComponent.initialize();
                expect(mockCustomerData.reload).toHaveBeenCalledWith(['wishlist'], false);
            });

            it('should reload wishlist if storeIds match and company is enabled', async function () {
                window.checkout = { storeId: 1 };
                mockCompany.is_enabled = true;
                await wishlistComponent.initialize();
                expect(mockCustomerData.reload).toHaveBeenCalledWith(['wishlist'], false);
            });
        });
    });
});
