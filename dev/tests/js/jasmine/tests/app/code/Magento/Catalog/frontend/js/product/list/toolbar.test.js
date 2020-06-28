/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Catalog/js/product/list/toolbar'
], function ($, productListToolbarForm) {
    'use strict';

    describe('Magento_Catalog/js/product/list/toolbar', function () {
        var toolbar;

        beforeEach(function () {
            toolbar = $('<div class="toolbar"></div>');
        });

        afterEach(function () {
            toolbar.remove();
        });

        it('Widget extends jQuery object', function () {
            expect($.mage.productListToolbarForm).toBeDefined();
        });

        it('Toolbar is initialized', function () {
            spyOn($.mage.productListToolbarForm.prototype, '_create');

            toolbar.productListToolbarForm();

            expect($.mage.productListToolbarForm.prototype._create).toEqual(jasmine.any(Function));
            expect($.mage.productListToolbarForm.prototype._create).toHaveBeenCalledTimes(1);
        });

        it('Toolbar is initialized once', function () {
            spyOn($.mage.productListToolbarForm.prototype, '_bind');
            var secondToolbar = $('<div class="toolbar"></div>');

            toolbar.productListToolbarForm();
            secondToolbar.productListToolbarForm();

            expect($.mage.productListToolbarForm.prototype._bind).toHaveBeenCalledTimes(4);
        });
    });
});
