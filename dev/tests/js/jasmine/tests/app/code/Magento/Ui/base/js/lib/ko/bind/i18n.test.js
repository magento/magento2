/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/knockout/bindings/i18n',
    'mage/translate'
], function (ko, $) {
    'use strict';

    describe('i18n binding', function () {
        var elWithStaticText = $('<span />'),
            elWithVariable = $('<span />'),
            staticText = 'staticText',
            variableText = 'variableText',
            variable = ko.observable(variableText),
            dataTranslateAttr = '[{"shown":"&","translated":"&","original":"$","location":"Span element"}]',
            dataTranslateAttrName = 'data-translate',
            context = require.s.contexts._,

            /** Stub */
            manageInlineTranslation = function (state) {
                context.config.config = {
                    'Magento_Ui/js/lib/knockout/bindings/i18n': {
                        inlineTranslation: !!state
                    }
                };
            },

            /** Stub */
            turnOnInlineTranslation = function () {
                manageInlineTranslation(true);
            },

            /** Stub */
            turnOffInlineTranslation = function () {
                manageInlineTranslation(false);
            };

        beforeEach(function () {
            $(document.body).append(elWithStaticText);
            $(document.body).append(elWithVariable);
        });

        afterEach(function () {
            elWithStaticText.remove();
            elWithVariable.remove();
        });

        it('if inline translation is off, just set text for element', function () {
            turnOffInlineTranslation();

            ko.applyBindingsToNode(elWithStaticText[0], {
                i18n: staticText
            });
            ko.applyBindingsToNode(elWithVariable[0], {
                i18n: variable
            });

            expect(elWithStaticText.text()).toEqual(staticText);
            expect(elWithVariable.text()).toEqual(variable());
            expect(elWithStaticText.attr(dataTranslateAttrName)).toBe(undefined);
            expect(elWithVariable.attr(dataTranslateAttrName)).toBe(undefined);
        });

        it('if inline translation is on, ' +
        'and there is no translation for this text, set original text for element', function () {
            turnOnInlineTranslation();

            ko.applyBindingsToNode(elWithStaticText[0], {
                i18n: staticText
            });
            ko.applyBindingsToNode(elWithVariable[0], {
                i18n: variable
            });

            expect(elWithStaticText.text()).toEqual(staticText);
            expect(elWithVariable.text()).toEqual(variableText);
            expect(elWithStaticText.attr(dataTranslateAttrName))
                .toEqual(dataTranslateAttr.replace(/\$/g, staticText).replace(/\&/g, staticText));
            expect(elWithVariable.attr(dataTranslateAttrName))
                .toEqual(dataTranslateAttr.replace(/\$/g, variableText).replace(/\&/g, variableText));
        });
    });
});
