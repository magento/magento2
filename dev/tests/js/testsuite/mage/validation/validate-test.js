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
 * @category    mage.validation
 * @package     test
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
MageValidationTest = TestCase('MageValidationTest');

MageValidationTest.prototype.testValidateNoHtmlTags = function () {
    assertEquals(true, $.validator.methods.validateNoHtmlTags(""));
    assertEquals(true, $.validator.methods.validateNoHtmlTags(null));
    assertEquals(true, $.validator.methods.validateNoHtmlTags("abc"));
    assertEquals(false, $.validator.methods.validateNoHtmlTags("<div>abc</div>"));
};

MageValidationTest.prototype.testAllowContainerClassName = function () {
    /*:DOC radio = <input type="radio" class="change-container-classname"/>*/
    assertEquals(true, $.validator.methods.allowContainerClassName(this.radio));
    /*:DOC checkbox = <input type="checkbox" class="change-container-classname"/>*/
    assertEquals(true, $.validator.methods.allowContainerClassName(this.checkbox));
    /*:DOC radio2 = <input type="radio"/>*/
    assertEquals(false, $.validator.methods.allowContainerClassName(this.radio2));
    /*:DOC checkbox2 = <input type="checkbox"/>*/
    assertEquals(false, $.validator.methods.allowContainerClassName(this.checkbox2));
};

MageValidationTest.prototype.testValidateSelect = function () {
    assertEquals(false, $.validator.methods.validateSelect(""));
    assertEquals(false, $.validator.methods.validateSelect("none"));
    assertEquals(false, $.validator.methods.validateSelect(null));
    assertEquals(false, $.validator.methods.validateSelect(undefined));
    assertEquals(true, $.validator.methods.validateSelect("abc"));
};

MageValidationTest.prototype.testIsEmpty = function () {
    assertEquals(true, $.validator.methods.isEmpty(""));
    assertEquals(true, $.validator.methods.isEmpty(null));
    assertEquals(true, $.validator.methods.isEmpty(undefined));
    assertEquals(true, $.validator.methods.isEmpty("   "));
};

MageValidationTest.prototype.testValidateAlphanumWithSpaces = function () {
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces(""));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces(null));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces(undefined));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces("   "));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces("abc   "));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces(" 123  "));
    assertEquals(true, $.validator.methods.validateAlphanumWithSpaces("  abc123 "));
    assertEquals(false, $.validator.methods.validateAlphanumWithSpaces("  !@# "));
    assertEquals(false, $.validator.methods.validateAlphanumWithSpaces("  abc.123 "));
};

MageValidationTest.prototype.testValidateStreet = function () {
    assertEquals(true, $.validator.methods.validateStreet(""));
    assertEquals(true, $.validator.methods.validateStreet(null));
    assertEquals(true, $.validator.methods.validateStreet(undefined));
    assertEquals(false, $.validator.methods.validateStreet("   "));
    assertEquals(true, $.validator.methods.validateStreet("1234 main st"));
    assertEquals(true, $.validator.methods.validateStreet("7700 w parmer ln"));
    assertEquals(true, $.validator.methods.validateStreet("7700 w parmer ln #125"));
    assertEquals(false, $.validator.methods.validateStreet("!@# w parmer ln $125"));
};

MageValidationTest.prototype.testValidatePhoneStrict = function () {
    assertEquals(true, $.validator.methods.validatePhoneStrict(""));
    assertEquals(true, $.validator.methods.validatePhoneStrict(null));
    assertEquals(true, $.validator.methods.validatePhoneStrict(undefined));
    assertEquals(false, $.validator.methods.validatePhoneStrict("   "));
    assertEquals(false, $.validator.methods.validatePhoneStrict("5121231234"));
    assertEquals(false, $.validator.methods.validatePhoneStrict("512.123.1234"));
    assertEquals(true, $.validator.methods.validatePhoneStrict("512-123-1234"));
    assertEquals(true, $.validator.methods.validatePhoneStrict("(512)123-1234"));
    assertEquals(true, $.validator.methods.validatePhoneStrict("(512) 123-1234"));
};

MageValidationTest.prototype.testValidatePhoneLax = function () {
    assertEquals(true, $.validator.methods.validatePhoneLax(""));
    assertEquals(true, $.validator.methods.validatePhoneLax(null));
    assertEquals(true, $.validator.methods.validatePhoneLax(undefined));
    assertEquals(false, $.validator.methods.validatePhoneLax("   "));
    assertEquals(true, $.validator.methods.validatePhoneLax("5121231234"));
    assertEquals(true, $.validator.methods.validatePhoneLax("512.123.1234"));
    assertEquals(true, $.validator.methods.validatePhoneLax("512-123-1234"));
    assertEquals(true, $.validator.methods.validatePhoneLax("(512)123-1234"));
    assertEquals(true, $.validator.methods.validatePhoneLax("(512) 123-1234"));
    assertEquals(true, $.validator.methods.validatePhoneLax("(512)1231234"));
    assertEquals(false, $.validator.methods.validatePhoneLax("(512)_123_1234"));
};

MageValidationTest.prototype.testValidateFax = function () {
    assertEquals(true, $.validator.methods.validateFax(""));
    assertEquals(true, $.validator.methods.validateFax(null));
    assertEquals(true, $.validator.methods.validateFax(undefined));
    assertEquals(false, $.validator.methods.validateFax("   "));
    assertEquals(false, $.validator.methods.validateFax("5121231234"));
    assertEquals(false, $.validator.methods.validateFax("512.123.1234"));
    assertEquals(true, $.validator.methods.validateFax("512-123-1234"));
    assertEquals(true, $.validator.methods.validateFax("(512)123-1234"));
    assertEquals(true, $.validator.methods.validateFax("(512) 123-1234"));
};

MageValidationTest.prototype.testValidateEmail = function () {
    assertEquals(true, $.validator.methods.validateEmail(""));
    assertEquals(true, $.validator.methods.validateEmail(null));
    assertEquals(true, $.validator.methods.validateEmail(undefined));
    assertEquals(false, $.validator.methods.validateEmail("   "));
    assertEquals(true, $.validator.methods.validateEmail("123@123.com"));
    assertEquals(true, $.validator.methods.validateEmail("abc@124.en"));
    assertEquals(true, $.validator.methods.validateEmail("abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmail("abc.abc.abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmail("abc.abc-abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmail("abc.abc_abc@abc.commmmm"));
    assertEquals(false, $.validator.methods.validateEmail("abc.abc_abc@abc"));
};

MageValidationTest.prototype.testValidateEmailSender = function () {
    assertEquals(true, $.validator.methods.validateEmailSender(""));
    assertEquals(true, $.validator.methods.validateEmailSender(null));
    assertEquals(true, $.validator.methods.validateEmailSender(undefined));
    assertEquals(true, $.validator.methods.validateEmailSender("   "));
    assertEquals(true, $.validator.methods.validateEmailSender("123@123.com"));
    assertEquals(true, $.validator.methods.validateEmailSender("abc@124.en"));
    assertEquals(true, $.validator.methods.validateEmailSender("abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmailSender("abc.abc.abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmailSender("abc.abc-abc@abc.commmmm"));
    assertEquals(true, $.validator.methods.validateEmailSender("abc.abc_abc@abc.commmmm"));
};

MageValidationTest.prototype.testValidatePassword = function () {
    assertEquals(true, $.validator.methods.validatePassword(""));
    assertEquals(false, $.validator.methods.validatePassword(null));
    assertEquals(false, $.validator.methods.validatePassword(undefined));
    assertEquals(true, $.validator.methods.validatePassword("   "));
    assertEquals(true, $.validator.methods.validatePassword("123@123.com"));
    assertEquals(false, $.validator.methods.validatePassword("abc"));
    assertEquals(false, $.validator.methods.validatePassword("abc       "));
    assertEquals(false, $.validator.methods.validatePassword("     abc      "));
    assertEquals(false, $.validator.methods.validatePassword("dddd"));
};

MageValidationTest.prototype.testValidateAdminPassword = function () {
    assertEquals(true, $.validator.methods.validateAdminPassword(""));
    assertEquals(false, $.validator.methods.validateAdminPassword(null));
    assertEquals(false, $.validator.methods.validateAdminPassword(undefined));
    assertEquals(true, $.validator.methods.validateAdminPassword("   "));
    assertEquals(true, $.validator.methods.validateAdminPassword("123@123.com"));
    assertEquals(false, $.validator.methods.validateAdminPassword("abc"));
    assertEquals(false, $.validator.methods.validateAdminPassword("abc       "));
    assertEquals(false, $.validator.methods.validateAdminPassword("     abc      "));
    assertEquals(false, $.validator.methods.validateAdminPassword("dddd"));
};

MageValidationTest.prototype.testValidateUrl = function () {
    assertEquals(true, $.validator.methods.validateUrl(""));
    assertEquals(true, $.validator.methods.validateUrl(null));
    assertEquals(true, $.validator.methods.validateUrl(undefined));
    assertEquals(false, $.validator.methods.validateUrl("   "));
    assertEquals(true, $.validator.methods.validateUrl("http://www.google.com"));
    assertEquals(true, $.validator.methods.validateUrl("http://127.0.0.1:8080/index.php"));
    assertEquals(true, $.validator.methods.validateUrl("http://app-spot.com/index.php"));
    assertEquals(true, $.validator.methods.validateUrl("http://app-spot_space.com/index.php"));
};

MageValidationTest.prototype.testValidateCleanUrl = function () {
    assertEquals(true, $.validator.methods.validateCleanUrl(""));
    assertEquals(true, $.validator.methods.validateCleanUrl(null));
    assertEquals(true, $.validator.methods.validateCleanUrl(undefined));
    assertEquals(false, $.validator.methods.validateCleanUrl("   "));
    assertEquals(true, $.validator.methods.validateCleanUrl("http://www.google.com"));
    assertEquals(false, $.validator.methods.validateCleanUrl("http://127.0.0.1:8080/index.php"));
    assertEquals(false, $.validator.methods.validateCleanUrl("http://127.0.0.1:8080"));
    assertEquals(false, $.validator.methods.validateCleanUrl("http://127.0.0.1"));
};

MageValidationTest.prototype.testValidateXmlIdentifier = function () {
    assertEquals(true, $.validator.methods.validateXmlIdentifier(""));
    assertEquals(true, $.validator.methods.validateXmlIdentifier(null));
    assertEquals(true, $.validator.methods.validateXmlIdentifier(undefined));
    assertEquals(false, $.validator.methods.validateXmlIdentifier("   "));
    assertEquals(true, $.validator.methods.validateXmlIdentifier("abc"));
    assertEquals(true, $.validator.methods.validateXmlIdentifier("abc_123"));
    assertEquals(true, $.validator.methods.validateXmlIdentifier("abc-123"));
    assertEquals(false, $.validator.methods.validateXmlIdentifier("123-abc"));
};

MageValidationTest.prototype.testValidateSsn = function () {
    assertEquals(true, $.validator.methods.validateSsn(""));
    assertEquals(true, $.validator.methods.validateSsn(null));
    assertEquals(true, $.validator.methods.validateSsn(undefined));
    assertEquals(false, $.validator.methods.validateSsn("   "));
    assertEquals(false, $.validator.methods.validateSsn("abc"));
    assertEquals(true, $.validator.methods.validateSsn("123-13-1234"));
    assertEquals(true, $.validator.methods.validateSsn("012-12-1234"));
    assertEquals(false, $.validator.methods.validateSsn("23-12-1234"));
};

MageValidationTest.prototype.testValidateZip = function () {
    assertEquals(true, $.validator.methods.validateZip(""));
    assertEquals(true, $.validator.methods.validateZip(null));
    assertEquals(true, $.validator.methods.validateZip(undefined));
    assertEquals(false, $.validator.methods.validateZip("   "));
    assertEquals(true, $.validator.methods.validateZip("12345-1234"));
    assertEquals(true, $.validator.methods.validateZip("02345"));
    assertEquals(false, $.validator.methods.validateZip("1234"));
    assertEquals(false, $.validator.methods.validateZip("1234-1234"));
};

MageValidationTest.prototype.testValidateDateAu = function () {
    assertEquals(true, $.validator.methods.validateDateAu(""));
    assertEquals(true, $.validator.methods.validateDateAu(null));
    assertEquals(true, $.validator.methods.validateDateAu(undefined));
    assertEquals(false, $.validator.methods.validateDateAu("   "));
    assertEquals(true, $.validator.methods.validateDateAu("01/01/2012"));
    assertEquals(true, $.validator.methods.validateDateAu("30/01/2012"));
    assertEquals(false, $.validator.methods.validateDateAu("01/30/2012"));
    assertEquals(false, $.validator.methods.validateDateAu("1/1/2012"));
};

MageValidationTest.prototype.testValidateCurrencyDollar = function () {
    assertEquals(true, $.validator.methods.validateCurrencyDollar(""));
    assertEquals(true, $.validator.methods.validateCurrencyDollar(null));
    assertEquals(true, $.validator.methods.validateCurrencyDollar(undefined));
    assertEquals(false, $.validator.methods.validateCurrencyDollar("   "));
    assertEquals(true, $.validator.methods.validateCurrencyDollar("$123"));
    assertEquals(true, $.validator.methods.validateCurrencyDollar("$1,123.00"));
    assertEquals(true, $.validator.methods.validateCurrencyDollar("$1234"));
    assertEquals(false, $.validator.methods.validateCurrencyDollar("$1234.1234"));
};

MageValidationTest.prototype.testValidateNotNegativeNumber = function () {
    assertEquals(true, $.validator.methods.validateNotNegativeNumber(""));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber(null));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber(undefined));
    assertEquals(false, $.validator.methods.validateNotNegativeNumber("   "));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber("0"));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber("1"));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber("1234"));
    assertEquals(true, $.validator.methods.validateNotNegativeNumber("1,234.1234"));
    assertEquals(false, $.validator.methods.validateNotNegativeNumber("-1"));
    assertEquals(false, $.validator.methods.validateNotNegativeNumber("-1e"));
    assertEquals(false, $.validator.methods.validateNotNegativeNumber("-1,234.1234"));
};

MageValidationTest.prototype.testValidateGreaterThanZero = function () {
    assertEquals(true, $.validator.methods.validateGreaterThanZero(""));
    assertEquals(true, $.validator.methods.validateGreaterThanZero(null));
    assertEquals(true, $.validator.methods.validateGreaterThanZero(undefined));
    assertEquals(false, $.validator.methods.validateGreaterThanZero("   "));
    assertEquals(false, $.validator.methods.validateGreaterThanZero("0"));
    assertEquals(true, $.validator.methods.validateGreaterThanZero("1"));
    assertEquals(true, $.validator.methods.validateGreaterThanZero("1234"));
    assertEquals(true, $.validator.methods.validateGreaterThanZero("1,234.1234"));
    assertEquals(false, $.validator.methods.validateGreaterThanZero("-1"));
    assertEquals(false, $.validator.methods.validateGreaterThanZero("-1e"));
    assertEquals(false, $.validator.methods.validateGreaterThanZero("-1,234.1234"));
};

MageValidationTest.prototype.testValidateCssLength = function () {
    assertEquals(true, $.validator.methods.validateCssLength(""));
    assertEquals(true, $.validator.methods.validateCssLength(null));
    assertEquals(true, $.validator.methods.validateCssLength(undefined));
    assertEquals(false, $.validator.methods.validateCssLength("   "));
    assertEquals(false, $.validator.methods.validateCssLength("0"));
    assertEquals(true, $.validator.methods.validateCssLength("1"));
    assertEquals(true, $.validator.methods.validateCssLength("1234"));
    assertEquals(true, $.validator.methods.validateCssLength("1,234.1234"));
    assertEquals(false, $.validator.methods.validateCssLength("-1"));
    assertEquals(false, $.validator.methods.validateCssLength("-1e"));
    assertEquals(false, $.validator.methods.validateCssLength("-1,234.1234"));
};

MageValidationTest.prototype.testValidateData = function () {
    assertEquals(true, $.validator.methods.validateData(""));
    assertEquals(true, $.validator.methods.validateData(null));
    assertEquals(true, $.validator.methods.validateData(undefined));
    assertEquals(false, $.validator.methods.validateData("   "));
    assertEquals(false, $.validator.methods.validateData("123abc"));
    assertEquals(true, $.validator.methods.validateData("abc"));
    assertEquals(false, $.validator.methods.validateData(" abc"));
    assertEquals(true, $.validator.methods.validateData("abc123"));
    assertEquals(false, $.validator.methods.validateData("abc-123"));
};