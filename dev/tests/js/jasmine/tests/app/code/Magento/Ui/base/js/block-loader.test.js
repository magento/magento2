/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'jquery'
], function (Squire, $) {
    'use strict';

    var blockLoaderTmpl = '<div data-role="loader" class="loading-mask" style="position: absolute;">\n' +
        '    <div class="loader">\n' +
        '        <img src="<%= loaderImageHref %>" alt="Loading..." title="Loading..." style="position: absolute;">\n' +
        '    </div>\n' +
        '</div>',
        injector = new Squire(),
        mocks = {
            'Magento_Ui/js/lib/knockout/template/loader': {
                /** Method stub. */
                loadTemplate: function () {
                    var defer = $.Deferred();

                    defer.resolve(blockLoaderTmpl);

                    return defer;
                }
            }
        },
        obj,
        ko;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Ui/js/block-loader', 'ko'], function (blockLoader, knockout) {
            obj = blockLoader;
            ko = knockout;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {
        }
    });

    describe('Magento_Ui/js/block-loader', function () {
        var blockContentLoadingClass = '_block-content-loading',
            loaderImageUrl = 'https://static.magento.com/loader.gif',
            element = $('<span data-bind="blockLoader: isLoading"/>'),
            isLoading,
            context;

        beforeEach(function () {
            isLoading = ko.observable();
            context = ko.bindingContext.prototype.createChildContext({
                isLoading: isLoading
            });
            obj(loaderImageUrl);
            $('body').append(element);
            ko.applyBindings(context, element[0]);
        });

        afterEach(function () {
            ko.cleanNode(element[0]);
            element.remove();
        });

        it('Check adding loader block to element', function () {
            isLoading(true);
            expect(element.hasClass(blockContentLoadingClass)).toBeTruthy();
            expect(element.children().attr('class')).toEqual('loading-mask');
            expect(element.find('img').attr('src')).toEqual(loaderImageUrl);
        });

        it('Check removing loader block from element', function () {
            isLoading(false);
            expect(element.hasClass(blockContentLoadingClass)).toBeFalsy();
            expect(element.children().length).toEqual(0);
        });
    });
});
