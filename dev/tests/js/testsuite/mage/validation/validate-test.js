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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
MageValidationTest = TestCase('MageValidationTest');

MageValidationTest.prototype.testValidateNoHtmlTags = function () {
    assertEquals(true, $.validator.methods['validate-no-html-tags'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-no-html-tags'].call(this, null));
    assertEquals(true, $.validator.methods['validate-no-html-tags'].call(this, "abc"));
    assertEquals(false, $.validator.methods['validate-no-html-tags'].call(this, "<div>abc</div>"));
};

MageValidationTest.prototype.testAllowContainerClassName = function () {
    /*:DOC radio = <input type="radio" class="change-container-classname"/>*/
    assertEquals(true, $.validator.methods['allow-container-className'].call(this, this.radio));
    /*:DOC checkbox = <input type="checkbox" class="change-container-classname"/>*/
    assertEquals(true, $.validator.methods['allow-container-className'].call(this, this.checkbox));
    /*:DOC radio2 = <input type="radio"/>*/
    assertEquals(false, $.validator.methods['allow-container-className'].call(this, this.radio2));
    /*:DOC checkbox2 = <input type="checkbox"/>*/
    assertEquals(false, $.validator.methods['allow-container-className'].call(this, this.checkbox2));
};

MageValidationTest.prototype.testValidateSelect = function () {
    assertEquals(false, $.validator.methods['validate-select'].call(this, ""));
    assertEquals(false, $.validator.methods['validate-select'].call(this, "none"));
    assertEquals(false, $.validator.methods['validate-select'].call(this, null));
    assertEquals(false, $.validator.methods['validate-select'].call(this, undefined));
    assertEquals(true, $.validator.methods['validate-select'].call(this, "abc"));
};

MageValidationTest.prototype.testValidateNotEmpty = function () {
    assertFalse($.validator.methods['validate-no-empty'].call(this, ""));
    assertFalse($.validator.methods['validate-no-empty'].call(this, null));
    assertFalse($.validator.methods['validate-no-empty'].call(this, undefined));
    assertFalse($.validator.methods['validate-no-empty'].call(this, "   "));
    assertTrue($.validator.methods['validate-no-empty'].call(this, "test"));
};

MageValidationTest.prototype.testValidateAlphanumWithSpaces = function () {
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, null));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, undefined));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, "abc   "));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, " 123  "));
    assertEquals(true, $.validator.methods['validate-alphanum-with-spaces'].call(this, "  abc123 "));
    assertEquals(false, $.validator.methods['validate-alphanum-with-spaces'].call(this, "  !@# "));
    assertEquals(false, $.validator.methods['validate-alphanum-with-spaces'].call(this, "  abc.123 "));
};

MageValidationTest.prototype.testValidateStreet = function () {
    assertEquals(true, $.validator.methods['validate-street'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-street'].call(this, null));
    assertEquals(true, $.validator.methods['validate-street'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-street'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-street'].call(this, "1234 main st"));
    assertEquals(true, $.validator.methods['validate-street'].call(this, "7700 w parmer ln"));
    assertEquals(true, $.validator.methods['validate-street'].call(this, "7700 w parmer ln #125"));
    assertEquals(false, $.validator.methods['validate-street'].call(this, "!@# w parmer ln $125"));
};

MageValidationTest.prototype.testValidatePhoneStrict = function () {
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, null));
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-phoneStrict'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-phoneStrict'].call(this, "5121231234"));
    assertEquals(false, $.validator.methods['validate-phoneStrict'].call(this, "512.123.1234"));
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, "512-123-1234"));
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, "(512)123-1234"));
    assertEquals(true, $.validator.methods['validate-phoneStrict'].call(this, "(512) 123-1234"));
};

MageValidationTest.prototype.testValidatePhoneLax = function () {
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, null));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-phoneLax'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "5121231234"));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "512.123.1234"));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "512-123-1234"));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "(512)123-1234"));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "(512) 123-1234"));
    assertEquals(true, $.validator.methods['validate-phoneLax'].call(this, "(512)1231234"));
    assertEquals(false, $.validator.methods['validate-phoneLax'].call(this, "(512)_123_1234"));
};

MageValidationTest.prototype.testValidateFax = function () {
    assertEquals(true, $.validator.methods['validate-fax'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-fax'].call(this, null));
    assertEquals(true, $.validator.methods['validate-fax'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-fax'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-fax'].call(this, "5121231234"));
    assertEquals(false, $.validator.methods['validate-fax'].call(this, "512.123.1234"));
    assertEquals(true, $.validator.methods['validate-fax'].call(this, "512-123-1234"));
    assertEquals(true, $.validator.methods['validate-fax'].call(this, "(512)123-1234"));
    assertEquals(true, $.validator.methods['validate-fax'].call(this, "(512) 123-1234"));
};

MageValidationTest.prototype.testValidateEmail = function () {
    assertEquals(true, $.validator.methods['validate-email'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-email'].call(this, null));
    assertEquals(true, $.validator.methods['validate-email'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-email'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "123@123.com"));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "abc@124.en"));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "abc.abc.abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "abc.abc-abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-email'].call(this, "abc.abc_abc@abc.commmmm"));
    assertEquals(false, $.validator.methods['validate-email'].call(this, "abc.abc_abc@abc"));
};

MageValidationTest.prototype.testValidateEmailSender = function () {
    assertEquals(true, $.validator.methods['validate-emailSender'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-emailSender'].call(null));
    assertEquals(true, $.validator.methods['validate-emailSender'].call(undefined));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("   "));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("123@123.com"));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("abc@124.en"));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("abc.abc.abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("abc.abc-abc@abc.commmmm"));
    assertEquals(true, $.validator.methods['validate-emailSender'].call("abc.abc_abc@abc.commmmm"));
};

MageValidationTest.prototype.testValidatePassword = function () {
    assertEquals(true, $.validator.methods['validate-password'].call(this, ""));
    assertEquals(false, $.validator.methods['validate-password'].call(this, null));
    assertEquals(false, $.validator.methods['validate-password'].call(this, undefined));
    assertEquals(true, $.validator.methods['validate-password'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-password'].call(this, "123@123.com"));
    assertEquals(false, $.validator.methods['validate-password'].call(this, "abc"));
    assertEquals(false, $.validator.methods['validate-password'].call(this, "abc       "));
    assertEquals(false, $.validator.methods['validate-password'].call(this, "     abc      "));
    assertEquals(false, $.validator.methods['validate-password'].call(this, "dddd"));
};

MageValidationTest.prototype.testValidateAdminPassword = function () {
    assertEquals(true, $.validator.methods['validate-admin-password'].call(this, ""));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, null));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, undefined));
    assertEquals(true, $.validator.methods['validate-admin-password'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-admin-password'].call(this, "123@123.com"));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, "abc"));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, "abc       "));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, "     abc      "));
    assertEquals(false, $.validator.methods['validate-admin-password'].call(this, "dddd"));
};

MageValidationTest.prototype.testValidateUrl = function () {
    assertEquals(true, $.validator.methods['validate-url'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-url'].call(this, null));
    assertEquals(true, $.validator.methods['validate-url'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-url'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-url'].call(this, "http://www.google.com"));
    assertEquals(true, $.validator.methods['validate-url'].call(this, "http://127.0.0.1:8080/index.php"));
    assertEquals(true, $.validator.methods['validate-url'].call(this, "http://app-spot.com/index.php"));
    assertEquals(true, $.validator.methods['validate-url'].call(this, "http://app-spot_space.com/index.php"));
};

MageValidationTest.prototype.testValidateCleanUrl = function () {
    assertEquals(true, $.validator.methods['validate-clean-url'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-clean-url'].call(this, null));
    assertEquals(true, $.validator.methods['validate-clean-url'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-clean-url'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-clean-url'].call(this, "http://www.google.com"));
    assertEquals(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1:8080/index.php"));
    assertEquals(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1:8080"));
    assertEquals(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1"));
};

MageValidationTest.prototype.testValidateXmlIdentifier = function () {
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, null));
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-xml-identifier'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, "abc"));
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, "abc_123"));
    assertEquals(true, $.validator.methods['validate-xml-identifier'].call(this, "abc-123"));
    assertEquals(false, $.validator.methods['validate-xml-identifier'].call(this, "123-abc"));
};

MageValidationTest.prototype.testValidateSsn = function () {
    assertEquals(true, $.validator.methods['validate-ssn'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-ssn'].call(this, null));
    assertEquals(true, $.validator.methods['validate-ssn'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-ssn'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-ssn'].call(this, "abc"));
    assertEquals(true, $.validator.methods['validate-ssn'].call(this, "123-13-1234"));
    assertEquals(true, $.validator.methods['validate-ssn'].call(this, "012-12-1234"));
    assertEquals(false, $.validator.methods['validate-ssn'].call(this, "23-12-1234"));
};

MageValidationTest.prototype.testValidateZip = function () {
    assertEquals(true, $.validator.methods['validate-zip'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-zip'].call(this, null));
    assertEquals(true, $.validator.methods['validate-zip'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-zip'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-zip'].call(this, "12345-1234"));
    assertEquals(true, $.validator.methods['validate-zip'].call(this, "02345"));
    assertEquals(false, $.validator.methods['validate-zip'].call(this, "1234"));
    assertEquals(false, $.validator.methods['validate-zip'].call(this, "1234-1234"));
};

MageValidationTest.prototype.testValidateDateAu = function () {
    assertEquals(true, $.validator.methods['validate-date-au'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-date-au'].call(this, null));
    assertEquals(true, $.validator.methods['validate-date-au'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-date-au'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-date-au'].call(this, "01/01/2012"));
    assertEquals(true, $.validator.methods['validate-date-au'].call(this, "30/01/2012"));
    assertEquals(false, $.validator.methods['validate-date-au'].call(this, "01/30/2012"));
    assertEquals(false, $.validator.methods['validate-date-au'].call(this, "1/1/2012"));
};

MageValidationTest.prototype.testValidateCurrencyDollar = function () {
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, null));
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-currency-dollar'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, "$123"));
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, "$1,123.00"));
    assertEquals(true, $.validator.methods['validate-currency-dollar'].call(this, "$1234"));
    assertEquals(false, $.validator.methods['validate-currency-dollar'].call(this, "$1234.1234"));
};

MageValidationTest.prototype.testValidateNotNegativeNumber = function () {
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, null));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-not-negative-number'].call(this, "   "));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, "0"));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, "1"));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, "1234"));
    assertEquals(true, $.validator.methods['validate-not-negative-number'].call(this, "1,234.1234"));
    assertEquals(false, $.validator.methods['validate-not-negative-number'].call(this, "-1"));
    assertEquals(false, $.validator.methods['validate-not-negative-number'].call(this, "-1e"));
    assertEquals(false, $.validator.methods['validate-not-negative-number'].call(this, "-1,234.1234"));
};

MageValidationTest.prototype.testValidateGreaterThanZero = function () {
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, null));
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-greater-than-zero'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-greater-than-zero'].call(this, "0"));
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, "1"));
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, "1234"));
    assertEquals(true, $.validator.methods['validate-greater-than-zero'].call(this, "1,234.1234"));
    assertEquals(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1"));
    assertEquals(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1e"));
    assertEquals(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1,234.1234"));
};

MageValidationTest.prototype.testValidateCssLength = function () {
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, null));
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-css-length'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-css-length'].call(this, "0"));
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, "1"));
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, "1234"));
    assertEquals(true, $.validator.methods['validate-css-length'].call(this, "1,234.1234"));
    assertEquals(false, $.validator.methods['validate-css-length'].call(this, "-1"));
    assertEquals(false, $.validator.methods['validate-css-length'].call(this, "-1e"));
    assertEquals(false, $.validator.methods['validate-css-length'].call(this, "-1,234.1234"));
};

MageValidationTest.prototype.testValidateData = function () {
    assertEquals(true, $.validator.methods['validate-data'].call(this, ""));
    assertEquals(true, $.validator.methods['validate-data'].call(this, null));
    assertEquals(true, $.validator.methods['validate-data'].call(this, undefined));
    assertEquals(false, $.validator.methods['validate-data'].call(this, "   "));
    assertEquals(false, $.validator.methods['validate-data'].call(this, "123abc"));
    assertEquals(true, $.validator.methods['validate-data'].call(this, "abc"));
    assertEquals(false, $.validator.methods['validate-data'].call(this, " abc"));
    assertEquals(true, $.validator.methods['validate-data'].call(this, "abc123"));
    assertEquals(false, $.validator.methods['validate-data'].call(this, "abc-123"));
};

MageValidationTest.prototype.testValidateOneRequiredByName = function () {
    /*:DOC += <input type="radio" name="radio" id="radio"/> */
    /*:DOC += <input type="radio" name="radio"/> */
    assertFalse($.validator.methods['validate-one-required-by-name'].call(this,
        null, document.getElementById('radio')));
    /*:DOC += <input type="radio" name="radio" checked/> */
    assertTrue($.validator.methods['validate-one-required-by-name'].call(this,
        null, document.getElementById('radio')));

    /*:DOC += <input type="checkbox" name="checkbox" id="checkbox"/> */
    /*:DOC += <input type="checkbox" name="checkbox"/> */
    assertFalse($.validator.methods['validate-one-required-by-name'].call(this,
        null, document.getElementById('checkbox')));
    /*:DOC += <input type="checkbox" name="checkbox" checked/> */
    assertTrue($.validator.methods['validate-one-required-by-name'].call(this,
        null, document.getElementById('checkbox')));
};

MageValidationTest.prototype.testLessThanEqualsTo = function () {
    /*:DOC += <input type="text" value=6 id="element1" />*/
    /*:DOC += <input type="text" value=5 id="element2" />*/
    var element1 = document.getElementById('element1');
    assertFalse($.validator.methods['less-than-equals-to'].call(this, element1.value,
        element1, '#element2'));
    element1.value = 4;
    assertTrue($.validator.methods['less-than-equals-to'].call(this, element1.value,
        element1, '#element2'));

    /*:DOC += <input type="text" id="element3" />*/
    /*:DOC += <input type="text" value=5 id="element4" />*/
    var element3 = document.getElementById('element3');
    assertTrue($.validator.methods['less-than-equals-to'].call(this, element3.value,
        element3, '#element4'));

    /*:DOC += <input type="text" value=6 id="element5" />*/
    /*:DOC += <input type="text" id="element6" />*/
    var element5 = document.getElementById('element5');
    assertTrue($.validator.methods['less-than-equals-to'].call(this, element5.value,
        element5, '#element6'));

    /*:DOC += <input type="text" value=20  id="element7" />*/
    /*:DOC += <input type="text" value=100 id="element8" />*/
    var element7 = document.getElementById('element7');
    assertTrue($.validator.methods['less-than-equals-to'].call(this, element7.value,
        element7, '#element8'));
};

MageValidationTest.prototype.testGreaterThanEqualsTo = function () {
    /*:DOC += <input type="text" value=6 id="element1" />*/
    /*:DOC += <input type="text" value=7 id="element2" />*/
    var element1 = document.getElementById('element1');
    assertFalse($.validator.methods['greater-than-equals-to'].call(this, element1.value,
        element1, '#element2'));
    element1.value = 9;
    assertTrue($.validator.methods['greater-than-equals-to'].call(this, element1.value,
        element1, '#element2'));

    /*:DOC += <input type="text" id="element3" />*/
    /*:DOC += <input type="text" value=5 id="element4" />*/
    var element3 = document.getElementById('element3');
    assertTrue($.validator.methods['greater-than-equals-to'].call(this, element3.value,
        element3, '#element4'));

    /*:DOC += <input type="text" value=6 id="element5" />*/
    /*:DOC += <input type="text" id="element6" />*/
    var element5 = document.getElementById('element5');
    assertTrue($.validator.methods['greater-than-equals-to'].call(this, element5.value,
        element5, '#element6'));

    /*:DOC += <input type="text" value=100 id="element7" />*/
    /*:DOC += <input type="text" value=20  id="element8" />*/
    var element7 = document.getElementById('element7');
    assertTrue($.validator.methods['greater-than-equals-to'].call(this, element7.value,
        element7, '#element8'));
};

MageValidationTest.prototype.testValidateGroupedQty = function () {
    /*:DOC += <div id="div1">
     <input type="text" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text"/>
     </div>
     */
    assertFalse($.validator.methods['validate-grouped-qty'].call(this, null, null, '#div1'));
    /*:DOC += <div id="div2">
     <input type="text" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text" value="a" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text"/>
     </div>
     */
    assertFalse($.validator.methods['validate-grouped-qty'].call(this, null, null, '#div2'));
    /*:DOC += <div id="div3">
     <input type="text" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text" value="-6" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text"/>
     </div>
     */
    assertFalse($.validator.methods['validate-grouped-qty'].call(this, null, null, '#div3'));
    /*:DOC += <div id="div4">
     <input type="text" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text" value="6" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text"/>
     </div>
     */
    assertTrue($.validator.methods['validate-grouped-qty'].call(this, null, null, '#div4'));
    /*:DOC += <div id="div5">
     <input type="text" value="1" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text" value="6" data-validate="{'validate-grouped-qty':'#super-product-table'}"/>
     <input type="text"/>
     </div>
     */
    assertTrue($.validator.methods['validate-grouped-qty'].call(this, null, null, '#div5'));

};

MageValidationTest.prototype.testValidateCCTypeSelect = function () {
    /*:DOC += <input id="visa-valid" type="text" value="4916808263499650"/>
     <input id="visa-invalid" type="text" value="1234567890123456"/>
     <input id="mc-valid" type="text" value="5203731841177490"/>
     <input id="mc-invalid" type="text" value="1111222233334444"/>
     <input id="ae-valid" type="text" value="376244899619217"/>
     <input id="ae-invalid" type="text" value="123451234512345"/>
     */
    assertTrue($.validator.methods['validate-cc-type-select'].call(this, 'VI', null, '#visa-valid'));
    assertFalse($.validator.methods['validate-cc-type-select'].call(this, 'VI', null, '#visa-invalid'));
    assertTrue($.validator.methods['validate-cc-type-select'].call(this, 'MC', null, '#mc-valid'));
    assertFalse($.validator.methods['validate-cc-type-select'].call(this, 'MC', null, '#mc-invalid'));
    assertTrue($.validator.methods['validate-cc-type-select'].call(this, 'AE', null, '#ae-valid'));
    assertFalse($.validator.methods['validate-cc-type-select'].call(this, 'AE', null, '#ae-invalid'));
};

MageValidationTest.prototype.testValidateCCNumber = function () {
    assertTrue($.validator.methods['validate-cc-number'].call(this, '4916835098995909', null, null));
    assertTrue($.validator.methods['validate-cc-number'].call(this, '5265071363284878', null, null));
    assertTrue($.validator.methods['validate-cc-number'].call(this, '6011120623356953', null, null));
    assertTrue($.validator.methods['validate-cc-number'].call(this, '371293266574617', null, null));
    assertFalse($.validator.methods['validate-cc-number'].call(this, '4916835098995901', null, null));
    assertFalse($.validator.methods['validate-cc-number'].call(this, '5265071363284870', null, null));
    assertFalse($.validator.methods['validate-cc-number'].call(this, '6011120623356951', null, null));
    assertFalse($.validator.methods['validate-cc-number'].call(this, '371293266574619', null, null));
};

MageValidationTest.prototype.testValidateCCType = function () {
    /*:DOC += <select id="cc-type">
     <option value=""></option>
     <option value="VI"></option>
     <option value="MC"></option>
     <option value="AE"></option>
     <option value="DI"></option>
     </select>
     */
    var ccType = $('#cc-type');
    ccType.val('VI');
    assertTrue($.validator.methods['validate-cc-type'].call(this, '4916835098995909', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, '#cc-type'));
    ccType.val('MC');
    assertTrue($.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-type'].call(this, '4916835098995909', null, '#cc-type'));
    ccType.val('AE');
    assertTrue($.validator.methods['validate-cc-type'].call(this, '371293266574617', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, '#cc-type'));
    ccType.val('DI');
    assertTrue($.validator.methods['validate-cc-type'].call(this, '6011000990139424', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-type'].call(this, '4916835098995909', null, '#cc-type'));
};

MageValidationTest.prototype.testValidateCCExp = function () {
    /*:DOC += <input id="year" type="text" value="4916808263499650"/>
     */
    var year = $('#year'),
        currentTime  = new Date(),
        currentMonth = currentTime.getMonth() + 1,
        currentYear  = currentTime.getFullYear();;
    year.val(currentYear);
    if (currentMonth > 1) {
        assertFalse($.validator.methods['validate-cc-exp'].call(this, currentMonth - 1, null, '#year'));
    }
    assertTrue($.validator.methods['validate-cc-exp'].call(this, currentMonth, null, '#year'));
    year.val(currentYear + 1);
    assertTrue($.validator.methods['validate-cc-exp'].call(this, currentMonth, null, '#year'));

};

MageValidationTest.prototype.testValidateCCCvn = function () {
    /*:DOC += <select id="cc-type">
     <option value=""></option>
     <option value="VI"></option>
     <option value="MC"></option>
     <option value="AE"></option>
     <option value="DI"></option>
     </select>
     */
    var ccType = $('#cc-type');
    ccType.val('VI');
    assertTrue($.validator.methods['validate-cc-cvn'].call(this, '123', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-cvn'].call(this, '1234', null, '#cc-type'));
    ccType.val('MC');
    assertTrue($.validator.methods['validate-cc-cvn'].call(this, '123', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-cvn'].call(this, '1234', null, '#cc-type'));
    ccType.val('AE');
    assertTrue($.validator.methods['validate-cc-cvn'].call(this, '1234', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-cvn'].call(this, '123', null, '#cc-type'));
    ccType.val('DI');
    assertTrue($.validator.methods['validate-cc-cvn'].call(this, '123', null, '#cc-type'));
    assertFalse($.validator.methods['validate-cc-cvn'].call(this, '1234', null, '#cc-type'));
};
