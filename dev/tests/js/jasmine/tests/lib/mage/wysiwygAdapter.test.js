/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

/* eslint-disable */
define([
    'wysiwygAdapter',
    'jquery',
    'tinymce'
], function (wysiwygAdapter, $, tinyMCE) {
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

    describe('wysiwygAdapter - enabled status', function () {
        var editor, textarea, originalActiveEditor;

        beforeEach(function () {
            editor = {
                initialized: true,
                mode: {
                    set: jasmine.createSpy('set')
                }
            };
            textarea = document.createElement('textarea');
            obj.id = 'editor-b';
            originalActiveEditor = tinyMCE.activeEditor;
            tinyMCE.activeEditor = editor;

            spyOn(tinyMCE, 'get').and.returnValue(editor);
            spyOn(obj, 'getTextArea').and.returnValue($(textarea));
            spyOn(obj, 'setToolbarStatus');
        });

        afterEach(function () {
            tinyMCE.activeEditor = originalActiveEditor;
        });

        it('switches the editor to readonly mode and disables the textarea and toolbar', function () {
            obj.setEnabledStatus(false);

            expect(editor.mode.set).toHaveBeenCalledWith('readonly');
            expect(textarea.disabled).toBe(true);
            expect(obj.setToolbarStatus).toHaveBeenCalledWith(false);
        });

        it('switches the editor to design mode and enables the textarea and toolbar', function () {
            textarea.disabled = true;

            obj.setEnabledStatus(true);

            expect(editor.mode.set).toHaveBeenCalledWith('design');
            expect(textarea.disabled).toBe(false);
            expect(obj.setToolbarStatus).toHaveBeenCalledWith(true);
        });

        it('disables its own editor when another editor is globally active', function () {
            var otherEditor = {
                id: 'editor-a',
                mode: {
                    set: jasmine.createSpy('setOtherEditorMode')
                }
            };

            tinyMCE.activeEditor = otherEditor;
            tinyMCE.get.and.callFake(function (id) {
                return id === 'editor-b' ? editor : otherEditor;
            });

            obj.setEnabledStatus(false);

            expect(tinyMCE.get).toHaveBeenCalledWith('editor-b');
            expect(editor.mode.set).toHaveBeenCalledWith('readonly');
            expect(otherEditor.mode.set).not.toHaveBeenCalled();
            expect(textarea.disabled).toBe(true);
        });

        it('disables the textarea without changing another editor before its own editor exists', function () {
            tinyMCE.get.and.returnValue(null);

            obj.setEnabledStatus(false);

            expect(editor.mode.set).not.toHaveBeenCalled();
            expect(obj.setToolbarStatus).not.toHaveBeenCalled();
            expect(textarea.disabled).toBe(true);
        });

        it('keeps the latest textarea state without requesting modes before initialization', function () {
            editor.initialized = false;

            obj.setEnabledStatus(false);
            obj.setEnabledStatus(true);

            expect(editor.mode.set).not.toHaveBeenCalled();
            expect(obj.setToolbarStatus).not.toHaveBeenCalled();
            expect(textarea.disabled).toBe(false);
        });

        it('applies readonly mode when the theme has no legacy toolbar panel', function () {
            editor.theme = {};
            obj.setToolbarStatus.and.callThrough();

            obj.setEnabledStatus(false);

            expect(editor.mode.set).toHaveBeenCalledWith('readonly');
            expect(textarea.disabled).toBe(true);
        });
    });

    describe('wysiwygAdapter - initialization disabled state', function () {
        var editor, textarea, initHandler, initializationObserver;

        beforeEach(function () {
            textarea = document.createElement('textarea');
            obj.id = 'editor-b';
            obj.config = {tinymce: {}};
            initializationObserver = jasmine.createSpy('initializationObserver');
            obj.eventBus = {fireEvent: initializationObserver};
            editor = {
                mode: {set: jasmine.createSpy('set')},
                on: function (event, handler) {
                    if (event === 'init') {
                        initHandler = handler;
                    }
                }
            };

            spyOn(obj, 'getTextArea').and.returnValue($(textarea));
            spyOn(obj, 'setToolbarStatus');
            obj.getSettings().setup(editor);
        });

        it('applies readonly mode and toolbar state before notifying initialization observers', function () {
            textarea.disabled = true;
            initializationObserver.and.callFake(function () {
                expect(editor.mode.set).toHaveBeenCalledWith('readonly');
                expect(obj.setToolbarStatus).toHaveBeenCalledWith(false);
            });

            initHandler({target: editor});

            expect(editor.mode.set).toHaveBeenCalledWith('readonly');
            expect(obj.setToolbarStatus).toHaveBeenCalledWith(false);
            expect(initializationObserver).toHaveBeenCalled();
        });

        it('leaves an enabled textarea editor in its default mode', function () {
            initHandler({target: editor});

            expect(editor.mode.set).not.toHaveBeenCalled();
            expect(obj.setToolbarStatus).not.toHaveBeenCalled();
            expect(initializationObserver).toHaveBeenCalled();
        });
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
});
