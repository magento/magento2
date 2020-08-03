/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Bundle/js/price-bundle',
    'Magento_Catalog/js/price-box'
], function ($) {
    'use strict';

    describe('Magento_Bundle/js/price-bundle', function () {

        var htmlContainer;

        beforeEach(function () {
            htmlContainer = $('<div class="price-final_price" data-role="priceBox"><ul class="price-box"></ul></div>');
        });

        afterEach(function () {
            htmlContainer.remove();
        });

        it('Widget extends jQuery object.', function () {
            expect($.fn.priceBundle).toBeDefined();
        });

        it('Check _updatePriceBox method call.', function () {

            spyOn($.mage.priceBundle.prototype, '_updatePriceBox');

            htmlContainer.priceBundle();

            expect($.mage.priceBundle.prototype._updatePriceBox).toEqual(jasmine.any(Function));
            expect($.mage.priceBundle.prototype._updatePriceBox).toHaveBeenCalledTimes(1);
        });

        it('Check _updatePriceBox method call after priceBox was initialized.', function () {
            spyOn($.mage.priceBundle.prototype, '_updatePriceBox').and.callThrough();
            htmlContainer.priceBundle();
            $('.price-box', htmlContainer).priceBox();
            expect($.mage.priceBundle.prototype._updatePriceBox).toEqual(jasmine.any(Function));
            expect($.mage.priceBundle.prototype._updatePriceBox).toHaveBeenCalledTimes(2);
        });

        it('Check _applyOptionNodeFix method doesn\'t call after priceBox initialization.', function () {
            var optionConfig = {
                    optionConfig: {
                        prices: {}
                    }
                },
                priceConfig = {
                    priceConfig: 10
                };

            spyOn($.mage.priceBundle.prototype, '_applyOptionNodeFix').and.callThrough();
            htmlContainer.priceBundle(optionConfig);
            $('.price-box', htmlContainer).priceBox(priceConfig);
            $('.price-box', htmlContainer).trigger('price-box-initialized');
            expect($.mage.priceBundle.prototype._applyOptionNodeFix).toEqual(jasmine.any(Function));
            expect($.mage.priceBundle.prototype._applyOptionNodeFix).toHaveBeenCalledTimes(2);
        });

        it('Check _updatePriceBox method call before priceBox was initialized.', function () {
            spyOn($.mage.priceBundle.prototype, '_updatePriceBox').and.callThrough();
            $('.price-box', htmlContainer).priceBox();
            htmlContainer.priceBundle();
            expect($.mage.priceBundle.prototype._updatePriceBox).toEqual(jasmine.any(Function));
            expect($.mage.priceBundle.prototype._updatePriceBox).toHaveBeenCalledTimes(1);
        });
    });
});
