/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'module',
    '../template/renderer',
    'mage/translate'
], function ($, ko, module, renderer) {
    'use strict';

    var locations = {
            'legend': 'Caption for the fieldset element',
            'label': 'Label for an input element.',
            'button': 'Push button',
            'a': 'Link label',
            'b': 'Bold text',
            'strong': 'Strong emphasized text',
            'i': 'Italic text',
            'em': 'Emphasized text',
            'u': 'Underlined text',
            'sup': 'Superscript text',
            'sub': 'Subscript text',
            'span': 'Span element',
            'small': 'Smaller text',
            'big': 'Bigger text',
            'address': 'Contact information',
            'blockquote': 'Long quotation',
            'q': 'Short quotation',
            'cite': 'Citation',
            'caption': 'Table caption',
            'abbr': 'Abbreviated phrase',
            'acronym': 'An acronym',
            'var': 'Variable part of a text',
            'dfn': 'Term',
            'strike': 'Strikethrough text',
            'del': 'Deleted text',
            'ins': 'Inserted text',
            'h1': 'Heading level 1',
            'h2': 'Heading level 2',
            'h3': 'Heading level 3',
            'h4': 'Heading level 4',
            'h5': 'Heading level 5',
            'h6': 'Heading level 6',
            'center': 'Centered text',
            'select': 'List options',
            'img': 'Image',
            'input': 'Form element'
        },

        /**
         * Generates [data-translate] attribute's value
         * @param {Object} translationData
         * @param {String} location
         */
        composeTranslateAttr = function (translationData, location) {
            var obj = [{
                'shown': translationData.shown,
                'translated': translationData.translated,
                'original': translationData.original,
                'location': locations[location] || 'Text'
            }];

            return JSON.stringify(obj);
        },

        /**
         * Sets text for the element
         * @param {Object} el
         * @param {String} text
         */
        setText = function (el, text) {
            $(el).text(text);
        },

        /**
         * Sets [data-translate] attribute for the element
         * @param {Object} el - The element which is binded
         * @param {String} original - The original value of the element
         */
        setTranslateProp = function (el, original) {
            var location = $(el).prop('tagName').toLowerCase(),
                translated = $.mage.__(original),
                translationData = {
                    shown: translated,
                    translated: translated,
                    original: original
                },
                translateAttr = composeTranslateAttr(translationData, location);

            $(el).attr('data-translate', translateAttr);

            setText(el, translationData.shown);
        },

        /**
         * Checks if node represents ko virtual node (nodeType === 8, nodeName === '#comment').
         *
         * @param {HTMLElement} node
         * @returns {Boolean}
         */
        isVirtualElement = function (node) {
            return node.nodeType === 8;
        },

        /**
        * Checks if it's real DOM element
        * in case of virtual element, returns span wrapper
        * @param {Object} el
        * @param {bool} isUpdate
        * @return {Object} el
        */
        getRealElement = function (el, isUpdate) {
            if (isVirtualElement(el)) {
                if (isUpdate) {
                    return $(el).next('span');
                }

                return $('<span/>').insertAfter(el);
            }

            return el;
        },

        /**
         * execute i18n binding
         * @param {Object} element
         * @param {Function} valueAccessor
         * @param {bool} isUpdate
         */
        execute = function (element, valueAccessor, isUpdate) {
            var original = ko.unwrap(valueAccessor() || ''),
                el = getRealElement(element, isUpdate),
                inlineTranslation = (module.config() || {}).inlineTranslation;

            if (inlineTranslation) {
                setTranslateProp(el, original);
            } else {
                setText(el, $.mage.__(original));
            }
        };

    /**
     * i18n binding
     * @property {Function}  init
     * @property {Function}  update
     */
    ko.bindingHandlers.i18n = {

        /**
         * init i18n binding
         * @param {Object} element
         * @param {Function} valueAccessor
         */
        init: function (element, valueAccessor) {
            execute(element, valueAccessor);
        },

        /**
         * update i18n binding
         * @param {Object} element
         * @param {Function} valueAccessor
         */
        update: function (element, valueAccessor) {
            execute(element, valueAccessor, true);
        }
    };

    ko.virtualElements.allowedBindings.i18n = true;

    renderer
        .addNode('translate', {
            binding: 'i18n'
        })
        .addAttribute('translate', {
            binding: 'i18n'
        });
});
