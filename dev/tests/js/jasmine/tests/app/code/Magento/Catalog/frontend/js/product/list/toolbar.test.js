/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_Catalog/js/product/list/toolbar',
    'mage/cookies'
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

        describe('changeUrl', function () {
            var submittedForm;

            /**
             * @param {Object} options
             * @return {Object}
             */
            function createWidget(options) {
                toolbar.productListToolbarForm($.extend({
                    url: '#',
                    formKey: 'cachedFormKey'
                }, options));

                return toolbar.data('mageProductListToolbarForm');
            }

            beforeEach(function () {
                submittedForm = null;
                spyOn(HTMLFormElement.prototype, 'submit').and.callFake(function () {
                    submittedForm = this;
                });
            });

            afterEach(function () {
                $(submittedForm).remove();
                window.location.hash = '';
            });

            it('Submits the form key from the cookie instead of the cached option', function () {
                spyOn($.mage.cookies, 'get').and.returnValue('cookieFormKey');

                createWidget({
                    post: true
                }).changeUrl('product_list_order', 'name', 'position');

                expect($.mage.cookies.get).toHaveBeenCalledWith('form_key');
                expect(submittedForm.elements['form_key'].value).toBe('cookieFormKey');
                expect(submittedForm.elements['product_list_order'].value).toBe('name');
            });

            it('Falls back to the form key option when the cookie is missing', function () {
                spyOn($.mage.cookies, 'get').and.returnValue(null);

                createWidget({
                    post: true
                }).changeUrl('product_list_order', 'name', 'position');

                expect(submittedForm.elements['form_key'].value).toBe('cachedFormKey');
            });

            it('Falls back to the form key option when the cookie is empty', function () {
                spyOn($.mage.cookies, 'get').and.returnValue('');

                createWidget({
                    post: true
                }).changeUrl('product_list_order', 'name', 'position');

                expect(submittedForm.elements['form_key'].value).toBe('cachedFormKey');
            });

            it('Changes the URL without a form or form key when post is disabled', function () {
                spyOn($.mage.cookies, 'get').and.returnValue('cookieFormKey');

                createWidget({
                    post: false
                }).changeUrl('product_list_order', 'name', 'position');

                expect(HTMLFormElement.prototype.submit).not.toHaveBeenCalled();
                expect($.mage.cookies.get).not.toHaveBeenCalled();
                expect(window.location.hash).toBe('#?product_list_order=name');
            });
        });
    });
});
