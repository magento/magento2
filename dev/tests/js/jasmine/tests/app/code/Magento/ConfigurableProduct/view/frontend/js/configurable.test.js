/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, Configurable) {
    'use strict';

    var widget,
        options = {
            'spConfig': {
                'attributes': {
                    '93': {
                        'id': '93',
                        'code': 'color',
                        'label': 'Color',
                        'options': [
                            {'id': '14', 'label': 'brown', 'products': ['2']},
                            {'id': '15', 'label': 'beige', 'products': ['3']}
                        ],
                        'position': '0'
                    }
                },
                'template': '$<%- data.price %>',
                'currencyFormat': '$%s',
                'optionPrices': {
                    '2': {
                        'baseOldPrice': {'amount': 12},
                        'oldPrice': {'amount': 12},
                        'basePrice': {'amount': 12},
                        'finalPrice': {'amount': 12},
                        'tierPrices': [{'qty': 4, 'price': 8.4, 'percentage': 30, 'excl_tax_price': 8.4}],
                        'msrpPrice': {'amount': 0}
                    },
                    '3': {
                        'baseOldPrice': {'amount': 10},
                        'oldPrice': {'amount': 10},
                        'basePrice': {'amount': 10},
                        'finalPrice': {'amount': 10},
                        'tierPrices': [],
                        'msrpPrice': {'amount': 0}
                    }
                },
                'priceFormat': {
                    'pattern': '$%s',
                    'precision': 2,
                    'requiredPrecision': 2,
                    'decimalSymbol': '.',
                    'groupSymbol': ',',
                    'groupLength': 3,
                    'integerRequired': false
                },
                'prices': {
                    'baseOldPrice': {'amount': 10},
                    'oldPrice': {'amount': 10},
                    'basePrice': {'amount': 10},
                    'finalPrice': {'amount': 10}
                },
                'productId': '4',
                'chooseText': 'Choose an Option...',
                'images': [],
                'index': { '2': {'93': '14'}, '3': {'93': '15'}},
                'salable': []
            }
        },
        blockHtml = '<div class="product-info-main">' +
            '<div class="product-info-price">' +
            '<div class="price-box price-final_price" data-role="priceBox">' +
            '</div>' +
            '</div>' +
            '<div class="product-add-form">' +
            '<form id="cart"/>' +
            '<select name=\'super_attribute[93]\'' +
            ' data-selector=\'super_attribute[93]\'' +
            ' data-validate=\'{required:true}\'' +
            ' id=\'attribute93\'' +
            ' class=\'super-attribute-select\'>' +
            '<option value=\'\'>Choose an Option...</option>' +
            '<option value=\'14\'>brown +$2.00</option>' +
            '<option value=\'15\'>beige</option>' +
            '</select>' +
            '<input id="qty"/>' +
            '</form>' +
            '</div>' +
            '</div>',
        selectElement,
        qtyElement,
        formElement;

    describe('Magento_ConfigurableProduct/js/configurable', function () {
        beforeEach(function () {
            $(blockHtml).appendTo('body');
            selectElement = $('#attribute93');
            qtyElement = $('#qty');
            formElement = $('#cart');
            $.fn.trigFunc = $.fn.trigger;
            $.fn.priceBoxFunc = $.fn.priceBox;
            $.fn.priceBox = function () {
                return {
                    prices: $.extend(true, {}, this.data('price-box'))
                };
            };
            $('body').find('[data-role=priceBox]').data('price-box', $.extend(true, {}, options.spConfig.prices));
            widget = new Configurable($.extend(true, {}, options), formElement);
        });
        afterEach(function () {
            formElement.remove();
            $.fn.trigger = $.fn.trigFunc;
            $.fn.priceBox = $.fn.priceBoxFunc;
            delete $.fn.trigFunc;
            delete $.fn.priceBoxFunc;
        });
        it('check if attribute value is possible to be set as configurable option', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('93=14');
            expect(widget.options.values['93']).toBe('14');
        });

        it('check that attribute value is not set if provided option does not exists', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('93=10');
            expect(widget.options.values['93']).toBe(undefined);
        });

        it('check if widget will return correct price values in case option is selected or not.', function () {
            var result;

            result = widget._getPrices().prices;
            expect(result.baseOldPrice.amount).toBe(0);
            expect(result.oldPrice.amount).toBe(0);
            expect(result.basePrice.amount).toBe(0);
            expect(result.finalPrice.amount).toBe(0);

            selectElement.val(14);
            result = widget._getPrices().prices;
            expect(result.baseOldPrice.amount).toBe(2);
            expect(result.oldPrice.amount).toBe(2);
            expect(result.basePrice.amount).toBe(2);
            expect(result.finalPrice.amount).toBe(2);
        });

        it('check that price is reloaded on qty change', function () {
            spyOn($.fn, 'trigger');
            qtyElement.trigFunc('input');
            expect($.fn.trigger)
                .toHaveBeenCalledWith(
                    'updatePrice',
                    {
                        prices: {
                            baseOldPrice: {
                                amount: 0
                            },
                            oldPrice: {
                                amount: 0
                            },
                            basePrice: {
                                amount: 0
                            },
                            finalPrice: {
                                amount: 0
                            }
                        }
                    }
                );
        });

        it('check if the _configureElement method is enabling configurable option or not', function () {
            selectElement.val(14);
            widget._configureElement(selectElement);
            expect(widget).toBeTruthy();
        });

        it('check if the _clearSelect method is clearing the option selections or not', function () {
            selectElement.empty();
            widget._clearSelect(selectElement);
            expect(widget).toBeTruthy();
        });

        it('check if the _getSimpleProductId method is returning simple product id or not', function () {
            widget._getSimpleProductId(selectElement);
            expect(widget).toBeTruthy();
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
                    widget.simpleProduct = 1;
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
