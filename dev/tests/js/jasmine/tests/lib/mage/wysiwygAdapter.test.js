/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

/* eslint-disable */
define([
    'wysiwygAdapter',
    'mage/adminhtml/tools'
], function (wysiwygAdapter) {
    'use strict';

    var obj;

    beforeEach(function () {

        /**
         * Dummy constructor to use for instantiation
         * @constructor
         */
        var Constr = function () {};

        Constr.prototype = wysiwygAdapter;

        obj = new Constr();
    });

    describe('wysiwygAdapter - encoding and decoding directives', function () {

        /**
         * Tests encoding and decoding directives
         *
         * @param {String} decodedHtml
         * @param {String} encodedHtml
         */
        function runTests(decodedHtml, encodedHtml) {
            var encodedHtmlWithForwardSlashInImgSrc = encodedHtml.replace(/src="([^"]+)/, 'src="$1/');

            describe('"encodeDirectives" method', function () {
                it('converts media directive img src to directive URL', function () {
                    expect(obj.encodeDirectives(decodedHtml)).toEqual(encodedHtml);
                });
            });

            describe('"decodeDirectives" method', function () {
                it(
                    'converts directive URL img src without a trailing forward slash ' +
                    'to media url without a trailing forward slash',
                    function () {
                        expect(obj.decodeDirectives(encodedHtml)).toEqual(decodedHtml);
                    }
                );

                it('converts directive URL img src with a trailing forward slash ' +
                    'to media url without a trailing forward slash',
                    function () {
                        expect(encodedHtmlWithForwardSlashInImgSrc).not.toEqual(encodedHtml);
                        expect(obj.decodeDirectives(encodedHtmlWithForwardSlashInImgSrc)).toEqual(decodedHtml);
                    }
                );
            });
        }

        describe('without SID in directive query string without secret key', function () {
            var decodedHtml = '<p>' +
                '<img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
                encodedHtml = '<p>' +
                    '<img src="http://example.com/admin/cms/wysiwyg/directive/___directive' +
                    '/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C" alt="" width="612" height="459">' +
                    '</p>';

            beforeEach(function () {
                obj.initialize('id', {
                    'directives_url': 'http://example.com/admin/cms/wysiwyg/directive/'
                });
            });

            runTests(decodedHtml, encodedHtml);
        });

        describe('without SID in directive query string with secret key', function () {
            var decodedHtml = '<p>' +
                '<img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
                encodedHtml = '<p>' +
                    '<img src="http://example.com/admin/cms/wysiwyg/directive/___directive' +
                    '/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C/key/' +
                    '5552655d13a141099d27f5d5b0c58869423fd265687167da12cad2bb39aa9a58" ' +
                    'alt="" width="612" height="459">' +
                    '</p>',
                directiveUrl = 'http://example.com/admin/cms/wysiwyg/directive/key/' +
                    '5552655d13a141099d27f5d5b0c58869423fd265687167da12cad2bb39aa9a58/';

            beforeEach(function () {
                obj.initialize('id', {
                    'directives_url': directiveUrl
                });
            });

            runTests(decodedHtml, encodedHtml);
        });

        describe('with SID in directive query string without secret key', function () {
            var decodedHtml = '<p>' +
                '<img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
                encodedHtml = '<p>' +
                    '<img src="http://example.com/admin/cms/wysiwyg/directive/___directive' +
                    '/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C?SID=something" ' +
                    'alt="" width="612" height="459">' +
                    '</p>',
                directiveUrl = 'http://example.com/admin/cms/wysiwyg/directive?SID=something';

            beforeEach(function () {
                obj.initialize('id', {
                    'directives_url': directiveUrl
                });
            });

            runTests(decodedHtml, encodedHtml);
        });

        describe('with SID in directive query string with secret key', function () {
            var decodedHtml = '<p>' +
                '<img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
                encodedHtml = '<p>' +
                    '<img src="http://example.com/admin/cms/wysiwyg/directive/___directive' +
                    '/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C/key/' +
                    '5552655d13a141099d27f5d5b0c58869423fd265687167da12cad2bb39aa9a58?SID=something" ' +
                    'alt="" width="612" height="459">' +
                    '</p>',
                directiveUrl = 'http://example.com/admin/cms/wysiwyg/directive/key/' +
                    '5552655d13a141099d27f5d5b0c58869423fd265687167da12cad2bb39aa9a58?SID=something';

            beforeEach(function () {
                obj.initialize('id', {
                    'directives_url': directiveUrl
                });
            });

            runTests(decodedHtml, encodedHtml);
        });
    });

    describe('WYSIWYG adapter directives', function () {
        beforeEach(function () {
            obj.config = {
                'add_directives': true,
                'directives_url': 'https://example.com/admin/cms/wysiwyg/directive/key/test/'
            };
        });

        [
            {
                name: 'a secure base URL with raw quotes',
                input: '<a href="{{config path="web/secure/base_url"}}test.html">Link</a>',
                expected: '<a href="{{config path=&quot;web/secure/base_url&quot;}}test.html">Link</a>'
            },
            {
                name: 'an unsecure base URL with encoded quotes',
                input: '<a href="{{config path=&quot;web/unsecure/base_url&quot;}}test.html">Link</a>',
                expected: '<a href="{{config path=&quot;web/unsecure/base_url&quot;}}test.html">Link</a>'
            },
            {
                name: 'a directive with a query string and fragment',
                input: '<a href="{{config path=&quot;web/secure/base_url&quot;}}test.html?a=1&amp;b=2#top">Link</a>',
                expected: '<a href="{{config path=&quot;web/secure/base_url&quot;}}test.html?a=1&amp;b=2#top">Link</a>'
            },
            {
                name: 'a store directive',
                input: '<a href="{{store url=&quot;test.html&quot;}}">Link</a>',
                expected: '<a href="{{store url=&quot;test.html&quot;}}">Link</a>'
            },
            {
                name: 'a media directive',
                input: '<a href="{{media url=&quot;wysiwyg/file.pdf&quot;}}">Link</a>',
                expected: '<a href="{{media url=&quot;wysiwyg/file.pdf&quot;}}">Link</a>'
            },
            {
                name: 'two directives with raw quotes',
                input: '<a href="{{config path="web/secure/base_url"}}{{store url="test.html"}}">Link</a>',
                expected: '<a href="{{config path=&quot;web/secure/base_url&quot;}}' +
                    '{{store url=&quot;test.html&quot;}}">Link</a>'
            },
            {
                name: 'an uppercase HTML attribute',
                input: '<a HREF="{{config path=&quot;web/secure/base_url&quot;}}">Link</a>',
                expected: '<a HREF="{{config path=&quot;web/secure/base_url&quot;}}">Link</a>'
            }
        ].forEach(function (testCase) {
            it('preserves ' + testCase.name + ' when loading and saving repeatedly', function () {
                var event = {content: testCase.input};

                obj.beforeSetContent(event);
                expect(event.content).toBe(testCase.expected);
                obj.saveContent(event);
                expect(event.content).toBe(testCase.expected);
                obj.beforeSetContent(event);
                expect(event.content).toBe(testCase.expected);
                obj.saveContent(event);
                expect(event.content).toBe(testCase.expected);
            });
        });

        [
            '<img src="{{media url=&quot;wysiwyg/image.jpg&quot;}}" alt="Image">',
            '<img src="{{config path=&quot;web/secure/base_url&quot;}}image.jpg" alt="Image">'
        ].forEach(function (content) {
            it('continues encoding and decoding preview URLs for ' + content, function () {
                var encoded = obj.encodeDirectives(content);

                expect(encoded).toContain('https://example.com/admin/cms/wysiwyg/directive/___directive/');
                expect(encoded).not.toContain('{{');
                expect(obj.decodeDirectives(encoded)).toBe(content);
            });
        });

        it('leaves ordinary links unchanged', function () {
            var content = '<a href="https://example.com/test.html?a=1&amp;b=2#top">Link</a>';

            expect(obj.encodeDirectives(content)).toBe(content);
            expect(obj.decodeDirectives(content)).toBe(content);
        });

        it('preserves config links alongside media previews', function () {
            var content = '<a href="{{config path=&quot;web/secure/base_url&quot;}}test.html">' +
                    '<img src="{{media url=&quot;wysiwyg/image.jpg&quot;}}"></a>',
                encoded = obj.encodeDirectives(content);

            expect(encoded).toContain('href="{{config path=&quot;web/secure/base_url&quot;}}test.html"');
            expect(encoded).toContain('src="https://example.com/admin/cms/wysiwyg/directive/___directive/');
            expect(obj.decodeDirectives(encoded)).toBe(content);
        });

        it('leaves content untouched when directive processing is disabled', function () {
            var event = {content: '<a href="{{config path=&quot;web/secure/base_url&quot;}}">Link</a>'};

            obj.config['add_directives'] = false;
            obj.beforeSetContent(event);
            expect(event.content).toBe('<a href="{{config path=&quot;web/secure/base_url&quot;}}">Link</a>');
            obj.saveContent(event);
            expect(event.content).toBe('<a href="{{config path=&quot;web/secure/base_url&quot;}}">Link</a>');
        });
    });
});
