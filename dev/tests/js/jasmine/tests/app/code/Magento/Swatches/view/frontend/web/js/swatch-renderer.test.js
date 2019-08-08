/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, SwatchRenderer) {
    'use strict';

    describe('Testing SwatchRenderer Widget', function () {
        var widget,
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
                options: [{id: optionId}]
            };
            widget.options = {
                classes: {optionClass: "swatch-option"},
                jsonSwatchConfig: {1: {2: {type: 2}}},
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
        });

        describe('"_RenderSwatchOptions" method', function () {
            var html,
                optionConfig;

            beforeEach(function () {
                optionConfig = widget.options.jsonSwatchConfig[attribute.id];
                html = $(widget._RenderSwatchOptions(attribute, 'option-label-control-id-1'))[0];
            });

            it('check first conditional statement', function () {
                expect(widget.options.jsonSwatchConfig.hasOwnProperty(attribute.id)).toEqual(true);
            });

            it('check second conditional statement', function () {
                expect(optionConfig.hasOwnProperty(optionId)).toEqual(true);
            });

            it('check swatch thumbnail image height attribute', function () {
                expect(html.hasAttribute('thumb-height')).toBe(true);
                expect(html.getAttribute('thumb-height')).toEqual(swathThumbImageHeight);
            });

            it('check swatch thumbnail image width attribute', function () {
                expect(html.hasAttribute('thumb-width')).toBe(true);
                expect(html.getAttribute('thumb-width')).toEqual(swathThumbImageWidth);
            });

            it('check swatch image styles', function () {
                expect(html.style.height).toEqual(swathImageHeight + 'px');
                expect(html.style.width).toEqual(swathImageWidth + 'px');
            });
        });
    });
});
