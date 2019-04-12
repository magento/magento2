/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'domReady',
    'consoleLogger',
    'jquery/ui',
    'mage/cookies'
], function ($, domReady, consoleLogger) {
    'use strict';

    /**
     * Helper. Generate random string
     * TODO: Merge with mage/utils
     * @param {String} chars - list of symbols
     * @param {Number} length - length for need string
     * @returns {String}
     */
    function generateRandomString(chars, length) {
        var result = '';

        length = length > 0 ? length : 1;

        while (length--) {
            result += chars[Math.round(Math.random() * (chars.length - 1))];
        }

        return result;
    }

    /**
     * Nodes tree to flat list converter
     * @returns {Array}
     */
    $.fn.comments = function () {
        var elements = [];

        /**
         * @param {jQuery} element - Comment holder
         */
        (function lookup(element) {
            var iframeHostName,
                contents,
                elementContents;

            // prevent cross origin iframe content reading
            if ($(element).prop('tagName') === 'IFRAME') {
                iframeHostName = $('<a>').prop('href', $(element).prop('src'))
                                             .prop('hostname');

                if (window.location.hostname !== iframeHostName) {
                    return [];
                }
            }

            /**
             * Rewrite jQuery contents method
             *
             * @param {Object} el
             * @returns {Object}
             * @private
             */
            contents = function (el) {
                return $.map(el, function (elem) {
                    try {
                        return $.nodeName(elem, 'iframe') ?
                               elem.contentDocument || (elem.contentWindow ? elem.contentWindow.document : []) :
                               $.merge([], elem.childNodes);
                    } catch (e) {
                        consoleLogger.error(e);

                        return [];
                    }
                });
            };

            elementContents = contents($(element));

            $.each(elementContents, function (index, el) {
                switch (el.nodeType) {
                    case 1: // ELEMENT_NODE
                        lookup(el);
                        break;

                    case 8: // COMMENT_NODE
                        elements.push(el);
                        break;

                    case 9: // DOCUMENT_NODE
                        lookup($(el).find('body'));
                        break;
                }
            });
        })(this);

        return elements;
    };

    /**
     * FormKey Widget - this widget is generating from key, saves it to cookie and
     */
    $.widget('mage.formKey', {
        options: {
            inputSelector: 'input[name="form_key"]',
            allowedCharacters: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            length: 16
        },

        /**
         * Creates widget 'mage.formKey'
         * @private
         */
        _create: function () {
            var formKey = $.mage.cookies.get('form_key');

            if (!formKey) {
                formKey = generateRandomString(this.options.allowedCharacters, this.options.length);
                $.mage.cookies.set('form_key', formKey);
            }
            $(this.options.inputSelector).val(formKey);
        }
    });

    /**
     * PageCache Widget
     * Handles additional ajax request for rendering user private content.
     */
    $.widget('mage.pageCache', {
        options: {
            url: '/',
            patternPlaceholderOpen: /^ BLOCK (.+) $/,
            patternPlaceholderClose: /^ \/BLOCK (.+) $/,
            versionCookieName: 'private_content_version',
            handles: []
        },

        /**
         * Creates widget 'mage.pageCache'
         * @private
         */
        _create: function () {
            var placeholders,
                version = $.mage.cookies.get(this.options.versionCookieName);

            if (!version) {
                return;
            }
            placeholders = this._searchPlaceholders(this.element.comments());

            if (placeholders && placeholders.length) {
                this._ajax(placeholders, version);
            }
        },

        /**
         * Parse page for placeholders.
         * @param {Array} elements
         * @returns {Array}
         * @private
         */
        _searchPlaceholders: function (elements) {
            var placeholders = [],
                tmp = {},
                ii,
                len,
                el, matches, name;

            if (!(elements && elements.length)) {
                return placeholders;
            }

            for (ii = 0, len = elements.length; ii < len; ii++) {
                el = elements[ii];
                matches = this.options.patternPlaceholderOpen.exec(el.nodeValue);
                name = null;

                if (matches) {
                    name = matches[1];
                    tmp[name] = {
                        name: name,
                        openElement: el
                    };
                } else {
                    matches = this.options.patternPlaceholderClose.exec(el.nodeValue);

                    if (matches) { //eslint-disable-line max-depth
                        name = matches[1];

                        if (tmp[name]) { //eslint-disable-line max-depth
                            tmp[name].closeElement = el;
                            placeholders.push(tmp[name]);
                            delete tmp[name];
                        }
                    }
                }
            }

            return placeholders;
        },

        /**
         * Parse for page and replace placeholders
         * @param {Object} placeholder
         * @param {Object} html
         * @protected
         */
        _replacePlaceholder: function (placeholder, html) {
            var startReplacing = false,
                prevSibling = null,
                parent, contents, yy, len, element;

            if (!placeholder || !html) {
                return;
            }

            parent = $(placeholder.openElement).parent();
            contents = parent.contents();

            for (yy = 0, len = contents.length; yy < len; yy++) {
                element = contents[yy];

                if (element == placeholder.openElement) { //eslint-disable-line eqeqeq
                    startReplacing = true;
                }

                if (startReplacing) {
                    $(element).remove();
                } else if (element.nodeType != 8) { //eslint-disable-line eqeqeq
                    //due to comment tag doesn't have siblings we try to find it manually
                    prevSibling = element;
                }

                if (element == placeholder.closeElement) { //eslint-disable-line eqeqeq
                    break;
                }
            }

            if (prevSibling) {
                $(prevSibling).after(html);
            } else {
                $(parent).prepend(html);
            }

            // trigger event to use mage-data-init attribute
            $(parent).trigger('contentUpdated');
        },

        /**
         * AJAX helper
         * @param {Object} placeholders
         * @param {String} version
         * @private
         */
        _ajax: function (placeholders, version) {
            var ii,
                data = {
                    blocks: [],
                    handles: this.options.handles,
                    originalRequest: this.options.originalRequest,
                    version: version
                };

            for (ii = 0; ii < placeholders.length; ii++) {
                data.blocks.push(placeholders[ii].name);
            }
            data.blocks = JSON.stringify(data.blocks.sort());
            data.handles = JSON.stringify(data.handles);
            data.originalRequest = JSON.stringify(data.originalRequest);
            $.ajax({
                url: this.options.url,
                data: data,
                type: 'GET',
                cache: true,
                dataType: 'json',
                context: this,

                /**
                 * Response handler
                 * @param {Object} response
                 */
                success: function (response) {
                    var placeholder, i;

                    for (i = 0; i < placeholders.length; i++) {
                        placeholder = placeholders[i];

                        if (response.hasOwnProperty(placeholder.name)) {
                            this._replacePlaceholder(placeholder, response[placeholder.name]);
                        }
                    }
                }
            });
        }
    });

    domReady(function () {
        $('body')
            .formKey();
    });

    return {
        'pageCache': $.mage.pageCache,
        'formKey': $.mage.formKey
    };
});
