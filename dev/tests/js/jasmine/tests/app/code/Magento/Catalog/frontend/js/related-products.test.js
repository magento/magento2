/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'Magento_Catalog/js/related-products'
], function ($) {
    'use strict';

    describe('Related Products Widget', function () {
        var widgetElement,
            selectAllLinkSelector = '[data-role="select-all"]',
            relatedCheckboxSelector = '.related-checkbox';

        beforeEach(function () {
            // Create elements and initialize widget manually
            widgetElement = $('<div>')
                .append('<a href="#" data-role="select-all">select all</a>')
                .append('<input type="checkbox" class="related-checkbox" value="1">')
                .append('<input type="checkbox" class="related-checkbox" value="2">')
                .append('<input type="hidden" id="related-products-field">')
                .appendTo('body');

            // Initialize the widget
            widgetElement.relatedProducts();
        });

        afterEach(function () {
            widgetElement.remove();
        });

        it('should select all related products when "Select All" is clicked', function () {
            $(selectAllLinkSelector).trigger('click');

            // Verify all checkboxes are checked
            expect($(relatedCheckboxSelector + ':checked').length).toBe(2);
        });

        it('should unselect all related products when "Unselect All" is clicked', function () {
            $(selectAllLinkSelector).trigger('click');
            $(selectAllLinkSelector).trigger('click');

            // Verify all checkboxes are unchecked
            expect($(relatedCheckboxSelector + ':checked').length).toBe(0);
        });

        it('should select all products, including those manually selected, when "Select All" is clicked',
            function () {
                $(relatedCheckboxSelector).first().prop('checked', true).trigger('click');
                $(selectAllLinkSelector).trigger('click');

                // Verify all products are selected, including the manually selected one
                expect($(relatedCheckboxSelector + ':checked').length).toBe(2);
            });

        it('should unselect all products when "Unselect All" is clicked after manual selection', function () {
            $(relatedCheckboxSelector).first().prop('checked', true).trigger('click');
            $(selectAllLinkSelector).trigger('click');
            $(selectAllLinkSelector).trigger('click');

            // Verify all checkboxes are unchecked
            expect($(relatedCheckboxSelector + ':checked').length).toBe(0);
        });
    });
});
