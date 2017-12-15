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
        injector.require(['Magento_Catalog/js/product/list/columns/final-price'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                links: '',
                listens: ''
            });
            done();
        });
    });

    describe('Magento_Catalog/js/product/list/columns/final-price', function () {
        describe('"getPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                    'price_info': {
                        'formatted_prices': {
                            'final_price': price
                        }
                    }
                };

                expect(obj.getPrice(row)).toBe(price);
            });
        });
        describe('"getRegularPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'formatted_prices': {
                                'regular_price': price
                            }
                        }
                    };

                expect(obj.getRegularPrice(row)).toBe(price);
            });
        });
        describe('"hasPriceRange" method', function () {
            it('Check returned value with equal mix_regular_price and max_regular_price', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'max_regular_price': price,
                            'min_regular_price': price
                        }
                    };

                expect(obj.hasPriceRange(row)).toBe(false);
            });
            it('Check returned value with different mix_regular_price and max_regular_price', function () {
                var row = {
                        'price_info': {
                            'max_regular_price': 100,
                            'min_regular_price': 200
                        }
                    };

                expect(obj.hasPriceRange(row)).toBe(true);
            });
        });
        describe('"hasSpecialPrice" method', function () {
            it('Check returned value with regular_price that is more than final_price', function () {
                var row = {
                        'price_info': {
                            'regular_price': 200,
                            'final_price': 100
                        }
                    };

                expect(obj.hasSpecialPrice(row)).toBe(true);
            });
            it('Check returned value with regular_price that is less than final_price', function () {
                var row = {
                    'price_info': {
                        'max_regular_price': 200,
                        'min_regular_price': 100
                    }
                };

                expect(obj.hasSpecialPrice(row)).toBe(false);
            });
        });
        describe('"isMinimalPrice" method', function () {
            it('Check returned value with minimal_price that is more than final_price', function () {
                var row = {
                    'price_info': {
                        'minimal_price': 200,
                        'final_price': 100
                    }
                };

                expect(obj.isMinimalPrice(row)).toBe(false);
            });
            it('Check returned value with minimal_price that is less than final_price', function () {
                var row = {
                    'price_info': {
                        'minimal_price': 100,
                        'final_price': 200
                    }
                };

                expect(obj.isMinimalPrice(row)).toBe(true);
            });
        });
        describe('"getMinimalPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                    'price_info': {
                        'formatted_prices': {
                            'minimal_price': price
                        }
                    }
                };

                expect(obj.getMinimalPrice(row)).toBe(price);
            });
        });
        describe('"isSalable" method', function () {
            it('Check returned value', function () {
                var salable = true,
                    row = {
                        'is_salable': salable
                    };

                expect(obj.isSalable(row)).toBe(salable);
            });
        });
        describe('"getMaxPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'formatted_prices': {
                                'max_price': price
                            }
                        }
                    };

                expect(obj.getMaxPrice(row)).toBe(price);
            });
        });
        describe('"getMaxRegularPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'formatted_prices': {
                                'max_regular_price': price
                            }
                        }
                    };

                expect(obj.getMaxRegularPrice(row)).toBe(price);
            });
        });
        describe('"getMinRegularPrice" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'formatted_prices': {
                                'min_regular_price': price
                            }
                        }
                    };

                expect(obj.getMinRegularPrice(row)).toBe(price);
            });
        });
        describe('"getAdjustmentCssClasses" method', function () {
            var price = 100,
                row = {
                    'price_info': {
                        'formatted_prices': {
                            'min_regular_price': price
                        }
                    }
                };

            beforeEach(function () {
                obj.getAdjustments = jasmine.createSpy().and.returnValue([{
                    index: 1
                }]);
            });

            it('Check returned value', function () {
                expect(obj.getAdjustmentCssClasses(row)).toBe('1');
            });
            it('Check calls "getAdjustments" method', function () {
                obj.getAdjustmentCssClasses(row);
                expect(obj.getAdjustments).toHaveBeenCalled();
            });
        });
        describe('"getMinimalPriceAmount" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'minimal_price': price
                        }
                    };

                expect(obj.getMinimalPriceAmount(row)).toBe(price);
            });
        });
        describe('"getMinimalRegularPriceAmount" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'min_regular_price': price
                        }
                    };

                expect(obj.getMinimalRegularPriceAmount(row)).toBe(price);
            });
        });
        describe('"getMaximumPriceAmount" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'max_price': price
                        }
                    };

                expect(obj.getMaximumPriceAmount(row)).toBe(price);
            });
        });
        describe('"getMaximumRegularPriceAmount" method', function () {
            it('Check returned value', function () {
                var price = 100,
                    row = {
                        'price_info': {
                            'max_regular_price': price
                        }
                    };

                expect(obj.getMaximumRegularPriceAmount(row)).toBe(price);
            });
        });
        describe('"showMinRegularPrice" method', function () {
            it('Check call "getMinimalPriceAmount" and "getMinimalRegularPriceAmount" methods', function () {
                var row = {};

                obj.getMinimalPriceAmount = jasmine.createSpy();
                obj.getMinimalRegularPriceAmount = jasmine.createSpy();
                obj.showMinRegularPrice(row);

                expect(obj.getMinimalPriceAmount).toHaveBeenCalledWith(row);
                expect(obj.getMinimalRegularPriceAmount).toHaveBeenCalledWith(row);
            });

            it('Check returned value when minimal price amount is less than regular price amount', function () {
                obj.getMinimalPriceAmount = jasmine.createSpy().and.returnValue(1);
                obj.getMinimalRegularPriceAmount = jasmine.createSpy().and.returnValue(2);

                expect(obj.showMinRegularPrice()).toBe(true);
            });

            it('Check returned value when minimal price amount is more than regular price amount', function () {
                obj.getMinimalPriceAmount = jasmine.createSpy().and.returnValue(2);
                obj.getMinimalRegularPriceAmount = jasmine.createSpy().and.returnValue(1);

                expect(obj.showMinRegularPrice()).toBe(false);
            });
        });
        describe('"showMaxRegularPrice" method', function () {
            it('Check call "getMaximumPriceAmount" and "getMaximumRegularPriceAmount" methods', function () {
                var row = {};

                obj.getMaximumPriceAmount = jasmine.createSpy();
                obj.getMaximumRegularPriceAmount = jasmine.createSpy();
                obj.showMaxRegularPrice(row);

                expect(obj.getMaximumPriceAmount).toHaveBeenCalledWith(row);
                expect(obj.getMaximumRegularPriceAmount).toHaveBeenCalledWith(row);
            });

            it('Check returned value when maximal price amount is less than regular price amount', function () {
                obj.getMaximumPriceAmount = jasmine.createSpy().and.returnValue(1);
                obj.getMaximumRegularPriceAmount = jasmine.createSpy().and.returnValue(2);

                expect(obj.showMaxRegularPrice()).toBe(true);
            });

            it('Check returned value when maximal price amount is more than regular price amount', function () {
                obj.getMaximumPriceAmount = jasmine.createSpy().and.returnValue(2);
                obj.getMaximumRegularPriceAmount = jasmine.createSpy().and.returnValue(1);

                expect(obj.showMaxRegularPrice()).toBe(false);
            });
        });
        describe('"getBody" method', function () {
            it('Check returned value', function () {
                var result = 'template';

                obj.bodyTmpl = result;
                expect(obj.getBody()).toBe(result);
            });
        });
        describe('"getAdjustments" method', function () {
            var adjustment = {
                setPriceType: jasmine.createSpy()
            };

            beforeEach(function () {
                obj.elems = jasmine.createSpy().and.returnValue([
                    adjustment
                ]);
            });
            it('Check call elems property', function () {
                obj.getAdjustments();
                expect(obj.elems).toHaveBeenCalled();
            });
            it('Check call "setPriceType" value', function () {
                var arg = 'type';

                obj.priceType = arg;
                obj.getAdjustments();
                expect(adjustment.setPriceType).toHaveBeenCalledWith(arg);
            });
            it('Check returned value', function () {
                var source = {};

                obj.source = source;

                expect(obj.getAdjustments()[0].source).toBe(source);
            });
        });
    });
});
