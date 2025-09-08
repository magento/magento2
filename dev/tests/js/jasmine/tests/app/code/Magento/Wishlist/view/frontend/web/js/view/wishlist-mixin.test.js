/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Wishlist/js/view/wishlist-mixin'
], function ($, customerData, wishlistMixin) {
    'use strict';

    describe('Magento_Wishlist/js/view/wishlist-mixin', function () {
        var WishlistComponent, mixin, customerDataMock, wishlistDataMock, subscribeCallback;

        beforeEach(function () {
            // Mock customerData
            customerDataMock = {
                reload: jasmine.createSpy('reload'),
                get: jasmine.createSpy('get')
            };

            // Helper function to set up subscribe callback
            function setupSubscribeCallback(callback) {
                subscribeCallback = callback;
            }

            wishlistDataMock = {
                subscribe: jasmine.createSpy('subscribe').and.callFake(setupSubscribeCallback)
            };

            customerDataMock.get.and.returnValue(wishlistDataMock);

            // Mock the customerData module
            spyOn(customerData, 'reload').and.callFake(customerDataMock.reload);
            spyOn(customerData, 'get').and.callFake(customerDataMock.get);

            // Create a proper mock WishlistComponent with extend method and _super
            WishlistComponent = function () {};
            WishlistComponent.prototype = {
                initialize: function () {
                    return this;
                }
            };

            // Add the extend method that Magento components typically have
            WishlistComponent.extend = function (extensions) {
                var ExtendedComponent = function () {},
                    key,
                    methodName,
                    originalMethod;

                ExtendedComponent.prototype = Object.create(WishlistComponent.prototype);

                // Copy the extensions to the prototype
                for (key in extensions) {
                    if (extensions.hasOwnProperty(key)) {
                        ExtendedComponent.prototype[key] = extensions[key];
                    }
                }

                // Add the extend method to the new component
                ExtendedComponent.extend = WishlistComponent.extend;

                // Add _super method for each extended method
                for (methodName in extensions) {
                    if (!extensions.hasOwnProperty(methodName) || typeof extensions[methodName] !== 'function') {
                        continue;
                    }

                    originalMethod = WishlistComponent.prototype[methodName];
                    if (!originalMethod) {
                        continue;
                    }

                    // Use IIFE to create proper closure for _super method
                    (function (method) {
                        ExtendedComponent.prototype['_super'] = function () {
                            return method.apply(this, arguments);
                        };
                    })(originalMethod);
                }

                return ExtendedComponent;
            };

            // Apply the mixin
            mixin = wishlistMixin(WishlistComponent);
        });

        describe('Initialization', function () {
            it('should call _super() during initialization', function () {
                var instance = new mixin();

                spyOn(instance, '_super').and.callThrough();
                instance.initialize();

                expect(instance._super).toHaveBeenCalled();
            });

            it('should get wishlist data from customer data', function () {
                var instance = new mixin();

                instance.initialize();
                expect(customerData.get).toHaveBeenCalledWith('wishlist');
            });

            it('should return the instance after initialization', function () {
                var instance = new mixin(),
                    result = instance.initialize();

                expect(result).toBe(instance);
            });
        });

        describe('Wishlist subscription', function () {
            it('should subscribe to wishlist data updates', function () {
                var instance = new mixin();

                instance.initialize();
                expect(wishlistDataMock.subscribe).toHaveBeenCalledWith(jasmine.any(Function));
            });

            it('should handle undefined counter gracefully', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with undefined counter
                expect(function () {
                    subscribeCallback({ counter: undefined });
                }).not.toThrow();
            });

            it('should handle null counter gracefully', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with null counter
                expect(function () {
                    subscribeCallback({ counter: null });
                }).not.toThrow();
            });

            it('should handle missing counter property gracefully', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with no counter property
                expect(function () {
                    subscribeCallback({});
                }).not.toThrow();
            });
        });

        describe('Subscription callback behavior', function () {
            it('should handle counter updates when counters exist', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with counter update
                expect(function () {
                    subscribeCallback({ counter: 3 });
                }).not.toThrow();
            });

            it('should handle string counter values', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with string counter
                expect(function () {
                    subscribeCallback({ counter: '7' });
                }).not.toThrow();
            });

            it('should handle zero counter value', function () {
                var instance = new mixin();

                instance.initialize();
                // Call with zero counter
                expect(function () {
                    subscribeCallback({ counter: 0 });
                }).not.toThrow();
            });

            it('should handle multiple counter updates', function () {
                var instance = new mixin();

                instance.initialize();
                // Multiple updates
                expect(function () {
                    subscribeCallback({ counter: 1 });
                    subscribeCallback({ counter: 2 });
                    subscribeCallback({ counter: 0 });
                }).not.toThrow();
            });

            it('should handle rapid counter updates', function () {
                var instance = new mixin(),
                    i;

                instance.initialize();
                // Rapid updates
                expect(function () {
                    for (i = 0; i < 10; i++) {
                        subscribeCallback({ counter: i });
                    }
                }).not.toThrow();
            });
        });

        describe('Mixin functionality', function () {
            it('should extend the WishlistComponent correctly', function () {
                var instance = new mixin();

                expect(instance).toBeDefined();
                expect(typeof instance.initialize).toBe('function');
            });

            it('should maintain the original component structure', function () {
                var instance = new mixin();

                // The mixin should add functionality but not break the original
                expect(instance.initialize).toBeDefined();
            });

            it('should handle initialization multiple times', function () {
                var instance = new mixin(),
                    result1 = instance.initialize(), // First initialization
                    result2 = instance.initialize(); // Second initialization

                expect(result1).toBe(instance);
                expect(result2).toBe(instance);
            });
        });

        describe('Error handling', function () {
            it('should throw error when customerData.get returns null', function () {
                var instance = new mixin();

                customerDataMock.get.and.returnValue(null);
                expect(function () {
                    instance.initialize();
                }).toThrow();
            });

            it('should throw error when customerData.get returns undefined', function () {
                var instance = new mixin();

                customerDataMock.get.and.returnValue(undefined);
                expect(function () {
                    instance.initialize();
                }).toThrow();
            });

            it('should throw error when wishlist data has no subscribe method', function () {
                var instance = new mixin();

                customerDataMock.get.and.returnValue({});
                expect(function () {
                    instance.initialize();
                }).toThrow();
            });
        });
    });
});
