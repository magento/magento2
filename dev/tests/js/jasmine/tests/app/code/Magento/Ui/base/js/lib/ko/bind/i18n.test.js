/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/ko/bind/i18n',
    'mage/translate'
], function (ko, $) {
    'use strict';

    describe('i18n binding', function () {
        var elWithStaticText = $('<span />'),
            elWithVariable = $('<span />'),
            staticText = 'staticText',
            staticTextTranslated = 'staticTextTranslated',
            staticTextTranslatedRaw = '{{{staticTextTranslated}}{{staticTextTranslated}}{{staticText}}{{theme}}}',
            variableText = 'variableText',
            variable = ko.observable(variableText),
            variableTranslated = 'variableTextTranslated',
            variableTranslatedRaw = '{{{variableTextTranslated}}{{variableTextTranslated}}{{variableText}}{{theme}}}',
            dataTranslateAttr = '[{"shown":"&","translated":"&","original":"$","location":"Span element"}]',
            dataTranslateAttrName = 'data-translate',
            context = require.s.contexts._,
            manageInlineTranslation = function (state) {
                context.config.config = {
                    'Magento_Ui/js/lib/ko/bind/i18n': {
                        inlineTranslation: !!state
                    }
                };
            },
            turnOnInlineTranslation = function () {
                manageInlineTranslation(true);
            },
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

        it('if inline translation is on, ' +
        'and there is translation for this text,' +
        ' set translated text for element', function () {
            turnOnInlineTranslation();
            $.mage.translate.add(staticText, staticTextTranslatedRaw);
            $.mage.translate.add(variableText, variableTranslatedRaw);
            spyOn($.mage.translate, 'parsedTranslate').and.callThrough();

            context.config.config = {
                'Magento_Ui/js/lib/ko/bind/i18n': {
                    inlineTranslation: true
                }
            };

            ko.applyBindingsToNode(elWithStaticText[0], {
                i18n: staticText
            });
            ko.applyBindingsToNode(elWithVariable[0], {
                i18n: variable
            });

            expect($.mage.translate.parsedTranslate).toHaveBeenCalledWith(staticText);
            expect($.mage.translate.parsedTranslate).toHaveBeenCalledWith(variableText);
            expect(elWithStaticText.text()).toEqual(staticTextTranslated);
            expect(elWithVariable.text()).toEqual(variableTranslated);
            expect(elWithStaticText.attr(dataTranslateAttrName))
                .toEqual(dataTranslateAttr.replace(/\$/g, staticText).replace(/\&/g, staticTextTranslated));
            expect(elWithVariable.attr(dataTranslateAttrName))
                .toEqual(dataTranslateAttr.replace(/\$/g, variableText).replace(/\&/g, variableTranslated));
        });
    });
});
