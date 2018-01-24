/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'jquery',
    'jquery/ui'
], function (Squire, $) {
    'use strict';

    var injector = new Squire(),
        widget,
        menuContainer,
        mocks = {
            'Magento_Theme/js/model/breadcrumb-list': jasmine.createSpyObj(['push'])
        },
        defaultContext = require.s.contexts._,
        menuItem = $('<li class="level0"><a href="http://localhost.com/cat1.html" id="ui-id-3">Cat1</a></li>')[0],

        /**
         * Create context object.
         *
         * @param {Object} prototype
         * @param {*} options
         * @return {Object}
         */
        createContext = function (prototype, options) {
            options = options || {};

            return $.extend(Object.create(prototype), options);
        };

    beforeAll(function (done) {

        injector.mock(mocks);
        injector.require(
            [
                'Magento_Catalog/js/product/breadcrumbs',
                'Magento_Theme/js/view/breadcrumbs',
                'jquery/ui'
            ], function (mixin, breadcrumb) {
                widget = mixin(breadcrumb);
                done();
            }
        );
    });

    describe('Magento_Catalog/js/product/breadcrumbs', function () {
        it('mixin is applied to Magento_Theme/js/view/breadcrumbs', function () {
            var breadcrumbMixins = defaultContext.config.config.mixins['Magento_Theme/js/view/breadcrumbs'];

            expect(breadcrumbMixins['Magento_Catalog/js/product/breadcrumbs']).toBe(true);
        });

        describe('Check Magento_Catalog/js/product/breadcrumbs methods', function () {
            beforeEach(function () {
                menuContainer = $('<nav data-action="navigation"><ul></ul></nav>')[0];
                $(document.body).append(menuContainer);
            });

            afterEach(function () {
                menuContainer.remove();
            });

            it('Check _appendCatalogCrumbs call', function () {
                var categoryCrumb = {
                        'name': 'category100',
                        'link': window.location.href,
                        'title': 'Test'
                    },
                    context = {
                        options: {
                            product: 'simple'
                        }
                    },
                    appendCatalogCrumbsHandler;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._appendCatalogCrumbs).toBeDefined();

                $('[data-action="navigation"] > ul').html(menuItem);

                spyOn(widget.prototype, '_resolveCategoryCrumbs').and.returnValues([], [categoryCrumb]);
                spyOn(widget.prototype, '_getProductCrumb');
                spyOn(widget.prototype.options, 'product').and.returnValue('simple');

                context = createContext(widget.prototype, context);
                appendCatalogCrumbsHandler = widget.prototype._appendCatalogCrumbs.bind(context);
                appendCatalogCrumbsHandler();

                expect(widget.prototype._resolveCategoryCrumbs).toHaveBeenCalled();
                expect(mocks['Magento_Theme/js/model/breadcrumb-list'].push).toHaveBeenCalled();
                expect(mocks['Magento_Theme/js/model/breadcrumb-list'].push.calls.count()).toBe(1);
                mocks['Magento_Theme/js/model/breadcrumb-list'].push.calls.reset();

                appendCatalogCrumbsHandler();

                expect(widget.prototype._resolveCategoryCrumbs).toHaveBeenCalled();
                expect(mocks['Magento_Theme/js/model/breadcrumb-list'].push).toHaveBeenCalledWith(categoryCrumb);
                expect(mocks['Magento_Theme/js/model/breadcrumb-list'].push.calls.count()).toBe(2);
            });

            it('Check _getCategoryCrumb call', function () {
                var item = $('<a href="http://localhost.com/cat1.html" id="ui-id-3">Cat1</a>');

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._getCategoryCrumb).toBeDefined();
                expect(widget.prototype._getCategoryCrumb(item)).toEqual(jasmine.objectContaining(
                    {
                        'name': 'category3',
                        'label': 'Cat1',
                        'link': 'http://localhost.com/cat1.html'
                    }
                ));
            });

            it('Check _getProductCrumb call', function () {
                var context = {
                        options: {
                            product: 'simple'
                        }
                    },
                    getProductCrumbHandler;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._getProductCrumb).toBeDefined();

                getProductCrumbHandler = widget.prototype._getProductCrumb.bind(context);

                expect(getProductCrumbHandler()).toEqual(jasmine.objectContaining(
                    {
                        'name': 'product',
                        'label': 'simple'
                    }
                ));
            });

            it('Check _resolveCategoryMenuItem call with empty navigation menu', function () {
                var resolveCategoryMenuHandler,
                    context;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._resolveCategoryMenuItem).toBeDefined();

                context = createContext(widget.prototype);
                resolveCategoryMenuHandler = widget.prototype._resolveCategoryMenuItem.bind(context);

                spyOn(widget.prototype, '_resolveCategoryUrl').and.returnValue('');

                expect(resolveCategoryMenuHandler()).toBeNull();
            });

            it('Check _resolveCategoryMenuItem call with non-empty navigation menu', function () {
                var result,
                    context,
                    resolveCategoryMenuHandler;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._resolveCategoryMenuItem).toBeDefined();

                $('[data-action="navigation"] > ul').html(menuItem);

                spyOn(widget.prototype, '_resolveCategoryUrl').and.returnValue('http://localhost.com/cat1.html');

                context = createContext(widget.prototype);
                resolveCategoryMenuHandler = widget.prototype._resolveCategoryMenuItem.bind(context);
                result = resolveCategoryMenuHandler();

                expect(result).not.toBeNull();
                expect(result.length).toBe(1);
                expect(result[0].tagName.toLowerCase()).toEqual('a');
                expect(result[0].href).toEqual('http://localhost.com/cat1.html');
            });

            it('Check _resolveCategoryCrumbs call with empty navigation menu', function () {
                var result,
                    context,
                    resolveCategoryCrumbsHandler;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._resolveCategoryCrumbs).toBeDefined();

                spyOn(widget.prototype, '_resolveCategoryUrl').and.returnValue('');

                context = createContext(widget.prototype);
                resolveCategoryCrumbsHandler = widget.prototype._resolveCategoryCrumbs.bind(context);
                result = resolveCategoryCrumbsHandler();

                expect(result).toEqual(jasmine.any(Array));
                expect(result).toEqual([]);
            });

            it('Check _resolveCategoryCrumbs call with non-empty navigation menu', function () {
                var result,
                    context,
                    resolveCategoryCrumbsHandler;

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._resolveCategoryCrumbs).toBeDefined();

                $('[data-action="navigation"] > ul').html(menuItem);

                spyOn(widget.prototype, '_resolveCategoryUrl').and.returnValue('http://localhost.com/cat1.html');

                context = createContext(widget.prototype);
                resolveCategoryCrumbsHandler = widget.prototype._resolveCategoryCrumbs.bind(context);
                result = resolveCategoryCrumbsHandler();

                expect(result).not.toBeNull();
                expect(result).toEqual(jasmine.any(Array));
                expect(result.length).toBe(1);
                expect(result[0]).toEqual(jasmine.objectContaining(
                    {
                        'name': 'category3',
                        'label': 'Cat1',
                        'link': 'http://localhost.com/cat1.html'
                    }
                ));
            });

            it('Check _getParentMenuItem call', function () {
                var result,
                    menuItems = $(
                        '<li class="level0 nav-1">' +
                            '<a href="http://localhost.com/cat1.html" id="ui-id-3">cat1</a>' +
                            '<ul>' +
                                '<li class="level1 nav-1-1">' +
                                    '<a href="http://localhost.com/cat1/cat21.html" id="ui-id-9">cat21</a>' +
                                '</li>' +
                            '</ul>' +
                        '</li>'
                    ),
                    context,
                    getParentMenuHandler;

                $('[data-action="navigation"] > ul').html(menuItems);

                expect(widget).toBeDefined();
                expect(widget).toEqual(jasmine.any(Function));
                expect(widget.prototype._getParentMenuItem).toBeDefined();

                context = createContext(widget.prototype);
                getParentMenuHandler = widget.prototype._getParentMenuItem.bind(context);
                result = getParentMenuHandler($('#ui-id-9'));

                expect(result).toBeDefined();
                expect(result.length).toBe(1);
                expect(result[0].tagName.toLowerCase()).toEqual('a');
                expect(result.attr('id')).toEqual('ui-id-3');

                result = getParentMenuHandler($('#ui-id-3'));

                expect(result).toBeNull();
            });
        });
    });
});
