/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, SwatchRenderer) {
    'use strict';

    describe('Magento_Swatches/js/swatch-renderer.js', function () {
        var widget,
            html,
            optionConfig,
            attribute,
            optionId = 2,
            swathImageHeight = '60',
            swathImageWidth = '70',
            swathThumbImageHeight = '40',
            swathThumbImageWidth = '50',
            options,
            blockHtml = '<form id="cart"/>' +
                '<input id="qty"/>' +
                '</form>',
            qtyElement,
            formElement;

        beforeEach(function () {
            $(blockHtml).appendTo('body');
            qtyElement = $('#qty');
            formElement = $('#cart');
            attribute = {
                id: 1,
                options: [{
                    id: optionId
                }]
            };

            options = {
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

            widget = new SwatchRenderer(options);

            optionConfig = widget.options.jsonSwatchConfig[attribute.id];
            html = $(widget._RenderSwatchOptions(attribute, 'option-label-control-id-1'))[0];
        });

        afterEach(function () {
            formElement.remove();
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
            var productBlock, parentsResultMap, findResultMap;

            parentsResultMap = {
                '[data-role=priceBox]': {
                    priceBox: jasmine.createSpy().and.returnValue(''),
                    trigger: jasmine.createSpy(),
                    find: jasmine.createSpy().and.returnValue({
                        toggleClass: jasmine.createSpy()
                    })
                },
                '.normal-price .price-label': {
                    hide: jasmine.createSpy(),
                    show: jasmine.createSpy()
                },
                '.sly-old-price': {
                    hide: jasmine.createSpy()
                }
            };

            findResultMap = {
                '.swatch-attribute-options': [
                    $('<div class="swatch-attribute-options"><div class="swatch-option"></div></div>')
                ]
            };

            productBlock = {
                // eslint-disable-next-line max-nested-callbacks
                find: jasmine.createSpy().and.callFake(function (selector) {
                    return parentsResultMap[selector];
                })
            };

            widget.element =  {
                parents: jasmine.createSpy().and.returnValue(productBlock),
                // eslint-disable-next-line max-nested-callbacks
                find: jasmine.createSpy().and.callFake(function (selector) {
                    return findResultMap[selector];
                })
            };
            widget._getNewPrices  = jasmine.createSpy().and.returnValue(undefined);
            widget._UpdatePrice();
            expect(widget.element.parents).toHaveBeenCalledOnceWith('.product-info-main');
            expect(widget.element.find).toHaveBeenCalledOnceWith('.swatch-attribute-options');
            expect(parentsResultMap['[data-role=priceBox]'].find).toHaveBeenCalledOnceWith('span:first');
            expect(parentsResultMap['.sly-old-price'].hide).toHaveBeenCalledTimes(1);
            expect(parentsResultMap['.normal-price .price-label'].hide).toHaveBeenCalledTimes(1);
            expect(parentsResultMap['.normal-price .price-label'].hide)
                .toHaveBeenCalledBefore(parentsResultMap['.normal-price .price-label'].show);
            expect(parentsResultMap['.normal-price .price-label'].show).toHaveBeenCalledTimes(1);
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

        it('check that price is reloaded on qty change', function () {
            var priceBox = {
                    hide: jasmine.createSpy(),
                    priceBox: jasmine.createSpy().and.returnValue({ prices: {}}),
                    trigger: jasmine.createSpy(),
                    find: jasmine.createSpy().and.returnValue({
                        toggleClass: jasmine.createSpy()
                    })
                },
                productPriceMock = {
                    find: jasmine.createSpy().and.returnValue(priceBox)
                };

            widget.element =  {
                parents: jasmine.createSpy().and.returnValue(productPriceMock)
            };
            widget._getNewPrices  = jasmine.createSpy().and.returnValue({});
            widget._getPrices  = jasmine.createSpy().and.returnValue({});
            qtyElement.trigger('input');
            expect(priceBox.trigger).toHaveBeenCalledWith('updatePrice', { prices: {}});
        });

        describe('_addFotoramaVideoEvents', function () {
            let mageAddFotoramaVideoEventsWidget,
                jQueryFnAddFotoramaVideoEvents,
                jQueryFnOn,
                galleryElement;

            beforeEach(function () {
                mageAddFotoramaVideoEventsWidget = $.mage.AddFotoramaVideoEvents;
                jQueryFnAddFotoramaVideoEvents = $.fn.AddFotoramaVideoEvents;
                jQueryFnOn = $.fn.on;
                galleryElement = window.document.createElement('div');
                galleryElement.id = 'gallery-' + new Date().getTime();
                window.document.body.append(galleryElement);
            });

            afterEach(function () {
                $.mage.AddFotoramaVideoEvents = mageAddFotoramaVideoEventsWidget;
                $.fn.AddFotoramaVideoEvents = jQueryFnAddFotoramaVideoEvents;
                $.fn.on = jQueryFnOn;
                window.document.body.removeChild(galleryElement);
                galleryElement = null;
            });

            it('Should not call AddFotoramaVideoEvents if $.mage.AddFotoramaVideoEvents is undefined', function () {
                $.mage.AddFotoramaVideoEvents = undefined;
                $.fn.AddFotoramaVideoEvents = jasmine.createSpy().and.returnValue({});
                $.fn.on = jasmine.createSpy();

                widget.options.mediaGallerySelector = '#' + galleryElement.id;
                widget._addFotoramaVideoEvents();

                expect($.fn.AddFotoramaVideoEvents).not.toHaveBeenCalled();
                expect($.fn.on).toHaveBeenCalledWith('addfotoramavideoeventscreate', jasmine.any(Function));
            });

            it('Should not call AddFotoramaVideoEvents if the element has not been initialized', function () {
                $.mage.AddFotoramaVideoEvents = jasmine.createSpy();
                $.fn.AddFotoramaVideoEvents = jasmine.createSpy().and.returnValue(undefined);
                $.fn.on = jasmine.createSpy();

                widget.options.mediaGallerySelector = '#' + galleryElement.id;
                widget._addFotoramaVideoEvents();

                expect($.fn.AddFotoramaVideoEvents).toHaveBeenCalledTimes(1);
                expect($.fn.AddFotoramaVideoEvents).toHaveBeenCalledWith('instance');
                expect($.fn.on).toHaveBeenCalledWith('addfotoramavideoeventscreate', jasmine.any(Function));
            });

            it('Should call AddFotoramaVideoEvents immediately if the element has already been initialized',
                function () {
                    $.mage.AddFotoramaVideoEvents = jasmine.createSpy();
                    $.fn.AddFotoramaVideoEvents = jasmine.createSpy().and.returnValue({});

                    widget.options.mediaGallerySelector = '#' + galleryElement.id;
                    widget.options.gallerySwitchStrategy = 'prepend';
                    widget.getProduct = jasmine.createSpy().and.returnValue(1);
                    widget._addFotoramaVideoEvents();

                    expect($.fn.AddFotoramaVideoEvents).toHaveBeenCalledTimes(2);
                    expect($.fn.AddFotoramaVideoEvents).toHaveBeenCalledWith('instance');
                    expect($.fn.AddFotoramaVideoEvents).toHaveBeenCalledWith({
                        selectedOption: 1,
                        dataMergeStrategy: widget.options.gallerySwitchStrategy
                    });
                });
        });
    });
});
