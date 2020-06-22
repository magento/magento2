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
        var widget,
            wdContainer;

        beforeEach(function () {
            wdContainer = $('<div class="toolbar toolbar-products"></div>');
            widget = wdContainer.productListToolbarForm();
        });

        afterEach(function () {
            $(wdContainer).remove();
        });

        it('Widget extends jQuery object', function () {
            expect($.fn.productListToolbarForm).toBeDefined();
        });

        it('Toolbar is initialized', function () {
            expect(wdContainer.productListToolbarForm('option', 'isToolbarInitialized')).not.toBe(false);
        });
    });
});
