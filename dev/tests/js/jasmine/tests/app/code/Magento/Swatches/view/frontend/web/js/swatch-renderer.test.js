/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, SwatchRenderer) {
    'use strict';

    describe('Testing "_RenderSwatchOptions" method of SwatchRenderer Widget', function () {
        var widget,
            html,
            optionConfig,
            attribute,
            optionId = 2,
            swathImageHeight = '60',
            swathImageWidth = '70',
            swathThumbImageHeight = '40',
            swathThumbImageWidth = '50';

        beforeEach(function () {
            widget = new SwatchRenderer();
            attribute = {
                id: 1,
                options: [{
                    id: optionId
                }]
            };

            widget.options = {
                classes: {
                    optionClass: 'swatch-option'
                },
                jsonSwatchConfig: {
                    1: {
                        2: {
                            type: 2
                        }
                    }
                },
                jsonSwatchImageSizeConfig: {
                    swatchImage: {
                        width: swathImageWidth,
                        height: swathImageHeight
                    },
                    swatchThumb: {
                        width: swathThumbImageWidth,
                        height: swathThumbImageHeight
                    }
                }
            };

            optionConfig = widget.options.jsonSwatchConfig[attribute.id];
            html = $(widget._RenderSwatchOptions(attribute, 'option-label-control-id-1'))[0];
        });

        it('check if swatch config has attribute id', function () {
            expect(widget.options.jsonSwatchConfig.hasOwnProperty(attribute.id)).toEqual(true);
        });

        it('check if option config has option id', function () {
            expect(optionConfig.hasOwnProperty(optionId)).toEqual(true);
        });

        it('check swatch thumbnail image height attribute', function () {
            expect(html.hasAttribute('data-thumb-height')).toBe(true);
            expect(html.getAttribute('data-thumb-height')).toEqual(swathThumbImageHeight);
        });

        it('check swatch thumbnail image width attribute', function () {
            expect(html.hasAttribute('data-thumb-width')).toBe(true);
            expect(html.getAttribute('data-thumb-width')).toEqual(swathThumbImageWidth);
        });

        it('check swatch image styles', function () {
            expect(html.style.height).toEqual(swathImageHeight + 'px');
            expect(html.style.width).toEqual(swathImageWidth + 'px');
        });

        it('check udate price method', function () {
            var productPriceMock = {
                find: jasmine.createSpy().and.returnValue({
                    hide: jasmine.createSpy(),
                    priceBox: jasmine.createSpy().and.returnValue(''),
                    trigger: jasmine.createSpy(),
                    find: jasmine.createSpy().and.returnValue({
                        toggleClass: jasmine.createSpy()
                    })
                })
            };

            widget.element =  {
                parents: jasmine.createSpy().and.returnValue(productPriceMock)
            };
            widget._getNewPrices  = jasmine.createSpy().and.returnValue(undefined);
            widget._UpdatePrice();
            expect(productPriceMock.find().find.calls.count()).toBe(1);
        });

        it('check getSelectedOptionPriceIndex', function () {
            var optionMock = '<div class="swatch-attribute" data-attribute-id="2" data-option-selected="4"></div>',
                element = $('<div class="' + widget.options.tooltipClass +
                    '"><div class="image"></div><div class="title"></div><div class="corner"></div>' +
                    optionMock + '</div>'
                ),
                optionPricesMock = {
                    optionPrices: {
                        p: {
                            finalPrice: {
                                amount: 12
                            }
                        }
                    }
                };

            widget.element = element;
            widget.options.classes.attributeClass = 'swatch-attribute';
            widget.options.jsonConfig = optionPricesMock;
            widget.optionsMap = {
                2: {
                    4: {
                        products: 'p'
                    },
                    hasOwnProperty: jasmine.createSpy().and.returnValue(true)
                },
                hasOwnProperty: jasmine.createSpy().and.returnValue(true)
            };

            expect(widget._getSelectedOptionPriceIndex()).toBe('p');
        });
    });
});
