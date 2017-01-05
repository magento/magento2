/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
TranslateTest = TestCase('TranslateTest');
TranslateTest.prototype.testTranslateExist = function() {
    assertNotUndefined(jQuery.mage.translate);
};
TranslateTest.prototype.testTranslationParametersOneArgument = function() {
    jQuery.mage.translate.add('Hello World!');
    assertEquals(
        'Hello World!',
        jQuery.mage.translate.translate('Hello World!'));
};
TranslateTest.prototype.testTranslationParametersArray = function() {
    jQuery.mage.translate.add(['Hello World!', 'Bonjour tout le monde!']);
    assertEquals(
        'Hello World!',
        jQuery.mage.translate.translate('Hello World!'));
};
TranslateTest.prototype.testTranslationParametersObject = function() {
    var translation = {'Hello World!': 'Bonjour tout le monde!'};
    jQuery.mage.translate.add(translation);
    assertEquals(
        translation['Hello World!'],
        jQuery.mage.translate.translate('Hello World!'));

    translation = {
        'Hello World!': 'Hallo Welt!',
        'Some text with symbols!-+"%#*': 'Ein Text mit Symbolen!-+"%#*'
    };
    jQuery.mage.translate.add(translation);
    jQuery.each(translation, function(key) {
        assertEquals(translation[key], jQuery.mage.translate.translate(key));
    });
};
TranslateTest.prototype.testTranslationParametersTwoArguments = function() {
    jQuery.mage.translate.add('Hello World!', 'Bonjour tout le monde!');
    assertEquals(
        'Bonjour tout le monde!',
        jQuery.mage.translate.translate('Hello World!'));
};
TranslateTest.prototype.testTranslationAlias = function() {
    var translation = {'Hello World!': 'Bonjour tout le monde!'};
    jQuery.mage.translate.add(translation);
    assertEquals(translation['Hello World!'], jQuery.mage.__('Hello World!'));
};
