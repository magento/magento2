/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

/* eslint-disable */
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

    describe('wysiwygAdapter - preserving empty inline elements (icon fonts)', function () {
        /**
         * Builds a fake schema element rule that reports whether it was preserved.
         *
         * @return {Object}
         */
        function buildRule() {
            return {
                removeEmpty: true
            };
        }

        /**
         * Fake editor exposing "on" and a schema with i/em/span rules.
         *
         * @return {Object}
         */
        function buildFakeEditor() {
            var rules = {
                i: buildRule(),
                em: buildRule(),
                span: buildRule()
            };

            return {
                handlers: {},

                /**
                 * @param {String} event
                 * @param {Function} callback
                 */
                on: function (event, callback) {
                    this.handlers[event] = callback;
                },
                schema: {

                    /**
                     * @param {String} tagName
                     * @return {Object}
                     */
                    getElementRule: function (tagName) {
                        return rules[tagName];
                    }
                }
            };
        }

        beforeEach(function () {
            obj.initialize('wysiwyg-test-id', {
                tinymce: {
                    plugins: '',
                    toolbar: '',
                    'content_css': ''
                }
            });
        });

        it('clears removeEmpty for i/em/span once PreInit fires', function () {
            var editor = buildFakeEditor(),
                settings = obj.getSettings();

            settings.setup(editor);

            expect(typeof editor.handlers.PreInit).toEqual('function');

            // Not yet applied: PreInit hasn't fired.
            expect(editor.schema.getElementRule('i').removeEmpty).toEqual(true);

            editor.handlers.PreInit();

            expect(editor.schema.getElementRule('i').removeEmpty).toEqual(false);
            expect(editor.schema.getElementRule('em').removeEmpty).toEqual(false);
            expect(editor.schema.getElementRule('span').removeEmpty).toEqual(false);
        });

        it('does not throw when a tag has no schema rule', function () {
            var editor = buildFakeEditor(),
                settings = obj.getSettings();

            editor.schema.getElementRule = function () {
                return undefined;
            };
            settings.setup(editor);

            expect(function () {
                editor.handlers.PreInit();
            }).not.toThrow();
        });
    });
});
