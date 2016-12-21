/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_PageCache/js/page-cache'
], function ($) {
    'use strict';

    describe('Testing html-comments-parser $.fn.comments behavior', function () {
        var element,
            iframe,
            comment,
            host;

        beforeEach(function () {
            element = $('<div />');
            iframe = $('<iframe />');
            comment = '<!--COMMENT CONTENT-->';
            host = window.location.hostname;

            $('body')
                .append(element)
                .append(iframe);
        });

        afterEach(function () {
            $(element).remove();
            $(iframe).remove();
        });

        it('comments fn exists', function () {
            expect($.fn.comments).toBeDefined();
            expect($.fn.comments()).toEqual([]);
        });

        it('on empty node comments() returns empty Array', function () {
            expect($(element).comments()).toEqual([]);
            expect($(iframe).insertAfter('body').comments()).toEqual([]);
        });

        it('on non-empty node comments() returns empty Array with nodes', function () {
            element.html(comment);
            expect($(element).comments().length).toEqual(1);
            expect($(element).comments()[0].nodeType).toEqual(8);
            expect($(element).comments()[0].nodeValue).toEqual('COMMENT CONTENT');
        });

        it('on iframe from same host returns Array with nodes', function () {
            iframe.contents().find('body').html(comment);
            iframe.attr('src', '//' + host + '/');

            expect(iframe.comments().length).toEqual(1);
            expect(iframe.comments()[0].nodeType).toEqual(8);
            expect(iframe.comments()[0].nodeValue).toEqual('COMMENT CONTENT');
        });

        it('on iframe from other host returns empty Array', function () {
            iframe.contents().find('body').html(comment);
            iframe.attr('src', '//' + host + '.otherHost/');

            expect(iframe.comments().length).toEqual(0);
        });
    });

    describe('Testing FormKey Widget', function () {
        var wdContainer, inputContainer;

        beforeEach(function () {
            wdContainer = $('<div />');
            inputContainer = $('<input />');
        });

        afterEach(function () {
            $(wdContainer).remove();
            $(inputContainer).remove();
        });

        it('widget extends jQuery object', function () {
            expect($.fn.formKey).toBeDefined();
        });

        it('widget gets value of input[form_key]', function () {
            spyOn($.mage.cookies, 'get').and.returnValue('FAKE_COOKIE');

            wdContainer.formKey({
                'inputSelector': inputContainer
            });

            expect($.mage.cookies.get).toHaveBeenCalled();
            expect(inputContainer.val()).toBe('FAKE_COOKIE');
        });

        it('widget sets value to input[form_key] in case it empty', function () {
            spyOn($.mage.cookies, 'set');
            spyOn($.mage.cookies, 'get');

            wdContainer.formKey({
                'inputSelector': inputContainer
            });

            expect($.mage.cookies.get).toHaveBeenCalled();
            expect($.mage.cookies.set).toHaveBeenCalled();
            expect(inputContainer.val()).toEqual(jasmine.any(String));
        });

        it('widget exists on load on body', function (done) {
            $(function () {
                expect($('body').data('mageFormKey')).toBeDefined();
                done();
            });
        });
    });

    describe('Testing PageCache Widget', function () {
        var wdContainer, pageBlockContainer;

        beforeEach(function () {
            wdContainer = $('<div />');
            pageBlockContainer = $('<div />');
        });

        afterEach(function () {
            $(wdContainer).remove();
            $(pageBlockContainer).remove();
        });

        it('widget extends jQuery object', function () {
            expect($.fn.pageCache).toBeDefined();
        });

        it('widget breaks if no private_content_version cookie', function () {
            spyOn($.mage.cookies, 'get');
            spyOn($.fn, 'comments');

            wdContainer.pageCache();

            expect($.mage.cookies.get).toHaveBeenCalled();
            expect($.fn.comments).not.toHaveBeenCalled();
        });

        it('_searchPlaceholders is called only when HTML_COMMENTS', function () {
            var nodes;

            spyOn($.mage.cookies, 'get').and.returnValue('FAKE_VERSION_COOKIE');
            spyOn($.mage.pageCache.prototype, '_searchPlaceholders');

            wdContainer
                .html('<!-- BLOCK FAKE_BLOCK -->FAKE_TEXT<!-- /BLOCK FAKE_BLOCK -->')
                .pageCache();

            nodes = wdContainer.comments();
            expect(nodes.length).toEqual(2);

            expect($.mage.cookies.get).toHaveBeenCalled();
            expect($.mage.pageCache.prototype._searchPlaceholders).toHaveBeenCalled();
            expect($.mage.pageCache.prototype._searchPlaceholders).toHaveBeenCalledWith(nodes);
        });

        it('_searchPlaceholders returns Array of blocks', function () {
            var nodes,
                searches;

            spyOn($.mage.cookies, 'get').and.returnValue('FAKE_VERSION_COOKIE');

            wdContainer
                .html('<!-- BLOCK FAKE_BLOCK -->FAKE_TEXT<!-- /BLOCK FAKE_BLOCK -->')
                .pageCache();

            nodes = wdContainer.comments();
            searches = wdContainer.data('magePageCache')._searchPlaceholders(nodes);
            expect(wdContainer.data('magePageCache')._searchPlaceholders()).toEqual([]);
            expect(searches[0]).toEqual(jasmine.objectContaining({
                name: 'FAKE_BLOCK'
            }));
            expect(searches[0].openElement.nodeType).toBeDefined();
            expect(searches[0].closeElement.nodeType).toBeDefined();
        });

        it('_replacePlaceholder appends HTML after sibling node', function () {
            var replacer,
                searcher,
                placeholders,
                context;

            context = {
                options: {
                    patternPlaceholderOpen: /^ BLOCK (.+) $/,
                    patternPlaceholderClose: /^ \/BLOCK (.+) $/
                }
            };
            replacer = $.mage.pageCache.prototype._replacePlaceholder.bind(context);
            searcher = $.mage.pageCache.prototype._searchPlaceholders.bind(context);

            wdContainer
                .html('<span></span><!-- BLOCK FAKE_BLOCK -->FAKE_TEXT<!-- /BLOCK FAKE_BLOCK -->');
            placeholders = searcher(wdContainer.comments());
            replacer(placeholders[0], '<span>FAKE_HTML</span>');

            expect(wdContainer.html()).toEqual('<span></span><span>FAKE_HTML</span>');
        });

        it('_replacePlaceholder prepends HTML if no sibling', function () {
            var replacer,
                searcher,
                placeholders,
                context;

            context = {
                options: {
                    patternPlaceholderOpen: /^ BLOCK (.+) $/,
                    patternPlaceholderClose: /^ \/BLOCK (.+) $/
                }
            };
            replacer = $.mage.pageCache.prototype._replacePlaceholder.bind(context);
            searcher = $.mage.pageCache.prototype._searchPlaceholders.bind(context);

            wdContainer
                .html('<!-- BLOCK FAKE_BLOCK -->FAKE_TEXT<!-- /BLOCK FAKE_BLOCK -->');
            placeholders = searcher(wdContainer.comments());
            replacer(placeholders[0], '<span>FAKE_HTML</span>');

            expect(wdContainer.html()).toEqual('<span>FAKE_HTML</span>');
        });
    });
});
