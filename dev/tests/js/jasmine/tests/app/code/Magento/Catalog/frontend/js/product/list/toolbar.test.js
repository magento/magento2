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
        var widget;

        beforeEach(function () {
            widget = new productListToolbarForm();
        });

        it('Widget extends jQuery object', function () {
            expect($.mage.productListToolbarForm).toBeDefined();
        });

        it('Toolbar is initialized', function () {
            spyOn(widget, '_create');
            widget._create();
            expect(widget._create).toHaveBeenCalled();
        });
    });
});
