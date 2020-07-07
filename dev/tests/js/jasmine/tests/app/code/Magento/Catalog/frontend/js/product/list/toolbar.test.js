/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Catalog/js/product/list/toolbar'
], function ($) {
    'use strict';

    describe('Magento_Catalog/js/product/list/toolbar', function () {
        var widget,
            toolbar;

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

        it('Toolbar receives options properly', function () {
            toolbar.productListToolbarForm();
            expect(toolbar.productListToolbarForm('option', 'page')).toBe('p');
        });

        it('Toolbar receives element properly', function () {
            widget = toolbar.productListToolbarForm();
            expect(widget).toBe(toolbar);
        });
    });
});
