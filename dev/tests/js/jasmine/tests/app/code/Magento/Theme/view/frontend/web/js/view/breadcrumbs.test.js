/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Theme/js/view/breadcrumbs',
    'Magento_Theme/js/model/breadcrumb-list'
], function ($, breadcrumbs, breadcrumbList) {
    'use strict';

    describe('Magento_Theme/js/view/breadcrumbs', function () {
        var htmlContainer,
            defaultCrumb = {
                'name': 'home',
                'link': 'http://localhost.com',
                'title': 'Home',
                'label': 'Go to Home Page'
            };

        beforeEach(function () {
            htmlContainer = $('<div class="breadcrumbs"><ul class="items"></ul></div>');
        });

        afterEach(function () {
            htmlContainer.remove();
            breadcrumbList.pop();
        });

        it('Widget extends jQuery object.', function () {
            expect($.fn.breadcrumbs).toBeDefined();
        });

        it('Check _render method call.', function () {

            spyOn($.mage.breadcrumbs.prototype, '_render');

            htmlContainer.breadcrumbs();

            expect($.mage.breadcrumbs.prototype._render).toEqual(jasmine.any(Function));
            expect($.mage.breadcrumbs.prototype._render).toHaveBeenCalled();
        });

        it('Check breadcrumb render with empty breadcrumb list.', function () {

            htmlContainer.breadcrumbs();

            expect($(htmlContainer).find('li').length).toBe(0);
        });

        it('Check breadcrumb render with non-empty breadcrumb list.', function () {

            breadcrumbList.push(defaultCrumb);

            htmlContainer.breadcrumbs();

            expect($(htmlContainer).find('li').length).toBe(1);
            expect($(htmlContainer).html()).toContain('<li class="item home">');
        });
    });
});
