/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
