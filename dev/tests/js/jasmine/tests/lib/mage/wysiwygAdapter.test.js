/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'wysiwygAdapter'
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
            var encodedHtmlWithForwardSlashInImgSrc = encodedHtml.replace(/src="((?:(?!"|\\\?).)*)/, 'src="$1/');

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
});
