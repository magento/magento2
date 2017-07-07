/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
test( "testValidateNoHtmlTags", function() {
    expect(4);
    equal($.validator.methods['validate-no-html-tags'].call(this, ""),true);
    equal($.validator.methods['validate-no-html-tags'].call(this, null),true);
    equal($.validator.methods['validate-no-html-tags'].call(this, "abc"),true);
    equal($.validator.methods['validate-no-html-tags'].call(this, "<div>abc</div>"),false);

});

test( "testAllowContainerClassName", function() {
    expect(4);
    var radio = $('<input type="radio" class="change-container-classname"/>');
    radio.appendTo("#qunit-fixture");
    equal($.validator.methods['allow-container-className'].call(this, radio[0]),true);
    var checkbox = $('<input type="checkbox" class="change-container-classname"/>');
    equal($.validator.methods['allow-container-className'].call(this, checkbox[0]),true);
    var radio2 = $('<input type="radio"/>');
    equal($.validator.methods['allow-container-className'].call(this, radio2[0]),false);
    var checkbox2 = $('<input type="checkbox"/>');
    equal($.validator.methods['allow-container-className'].call(this, checkbox2[0]),false);
});

test( "testValidateSelect", function() {
    expect(5);
    equal($.validator.methods['validate-select'].call(this, ""),false);
    equal($.validator.methods['validate-select'].call(this, "none"),false);
    equal($.validator.methods['validate-select'].call(this, null),false);
    equal($.validator.methods['validate-select'].call(this, undefined),false);
    equal($.validator.methods['validate-select'].call(this, "abc"),true);
});

test( "testValidateNotEmpty", function() {
    expect(5);
    ok(!$.validator.methods['validate-no-empty'].call(this, ""));
    ok(!$.validator.methods['validate-no-empty'].call(this, null));
    ok(!$.validator.methods['validate-no-empty'].call(this, undefined));
    ok(!$.validator.methods['validate-no-empty'].call(this, "   "));
    ok($.validator.methods['validate-no-empty'].call(this, "test"));
});

test( "testValidateStreet", function() {
    expect(9);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, ""),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, null),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, undefined),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, "   "),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, "abc   "),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, " 123  "),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, "  abc123 "),true);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, "  !@# "),false);
    equal($.validator.methods['validate-alphanum-with-spaces'].call(this, "  abc.123 "),false);
});

test( "testValidatePhoneStrict", function() {
    expect(9);
    equal($.validator.methods['validate-phoneStrict'].call(this, ""),true);
    equal($.validator.methods['validate-phoneStrict'].call(this, null),true);
    equal($.validator.methods['validate-phoneStrict'].call(this, undefined),true);
    equal($.validator.methods['validate-phoneStrict'].call(this, "   "),false);
    equal($.validator.methods['validate-phoneStrict'].call(this, "5121231234"),false);
    equal($.validator.methods['validate-phoneStrict'].call(this, "512.123.1234"),false);
    equal($.validator.methods['validate-phoneStrict'].call(this, "512-123-1234"),true);
    equal($.validator.methods['validate-phoneStrict'].call(this, "(512)123-1234"),true);
    equal($.validator.methods['validate-phoneStrict'].call(this, "(512) 123-1234"),true);
});

test( "testValidatePhoneLax", function() {
    expect(11);
    equal($.validator.methods['validate-phoneLax'].call(this, ""),true);
    equal($.validator.methods['validate-phoneLax'].call(this, null),true);
    equal($.validator.methods['validate-phoneLax'].call(this, undefined),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "   "),false);
    equal($.validator.methods['validate-phoneLax'].call(this, "5121231234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "512.123.1234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "512-123-1234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "(512)123-1234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "(512) 123-1234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "(512)1231234"),true);
    equal($.validator.methods['validate-phoneLax'].call(this, "(512)_123_1234"),false);
});

test( "testValidateFax", function() {
    expect(9);
    equal($.validator.methods['validate-fax'].call(this, ""),true);
    equal($.validator.methods['validate-fax'].call(this, null),true);
    equal($.validator.methods['validate-fax'].call(this, undefined),true);
    equal($.validator.methods['validate-fax'].call(this, "   "),false);
    equal($.validator.methods['validate-fax'].call(this, "5121231234"),false);
    equal($.validator.methods['validate-fax'].call(this, "512.123.1234"),false);
    equal($.validator.methods['validate-fax'].call(this, "512-123-1234"),true);
    equal($.validator.methods['validate-fax'].call(this, "(512)123-1234"),true);
    equal($.validator.methods['validate-fax'].call(this, "(512) 123-1234"),true);
});

test( "testValidateEmail", function() {
    expect(11);
    equal($.validator.methods['validate-email'].call(this, ""),true);
    equal($.validator.methods['validate-email'].call(this, null),true);
    equal($.validator.methods['validate-email'].call(this, undefined),true);
    equal($.validator.methods['validate-email'].call(this, "   "),false);
    equal($.validator.methods['validate-email'].call(this, "123@123.com"),true);
    equal($.validator.methods['validate-email'].call(this, "abc@124.en"),true);
    equal($.validator.methods['validate-email'].call(this, "abc@abc.commmmm"),true);
    equal($.validator.methods['validate-email'].call(this, "abc.abc.abc@abc.commmmm"),true);
    equal($.validator.methods['validate-email'].call(this, "abc.abc-abc@abc.commmmm"),true);
    equal($.validator.methods['validate-email'].call(this, "abc.abc_abc@abc.commmmm"),true);
    equal($.validator.methods['validate-email'].call(this, "abc.abc_abc@abc"),false);
});

test( "testValidateEmailSender", function() {
    expect(10);
    equal($.validator.methods['validate-emailSender'].call(this, ""),true);
    equal($.validator.methods['validate-emailSender'].call(null),true);
    equal($.validator.methods['validate-emailSender'].call(undefined),true);
    equal($.validator.methods['validate-emailSender'].call("   "),true);
    equal($.validator.methods['validate-emailSender'].call("123@123.com"),true);
    equal($.validator.methods['validate-emailSender'].call("abc@124.en"),true);
    equal($.validator.methods['validate-emailSender'].call("abc@abc.commmmm"),true);
    equal($.validator.methods['validate-emailSender'].call("abc.abc.abc@abc.commmmm"),true);
    equal($.validator.methods['validate-emailSender'].call("abc.abc-abc@abc.commmmm"),true);
    equal($.validator.methods['validate-emailSender'].call("abc.abc_abc@abc.commmmm"),true);
});

test( "testValidatePassword", function() {
    expect(9);
    equal($.validator.methods['validate-password'].call(this, ""),true);
    equal($.validator.methods['validate-password'].call(this, null),false);
    equal($.validator.methods['validate-password'].call(this, undefined),false);
    equal($.validator.methods['validate-password'].call(this, "   "),true);
    equal($.validator.methods['validate-password'].call(this, "123@123.com"),true);
    equal($.validator.methods['validate-password'].call(this, "abc"),false);
    equal($.validator.methods['validate-password'].call(this, "abc       "),false);
    equal($.validator.methods['validate-password'].call(this, "     abc      "),false);
    equal($.validator.methods['validate-password'].call(this, "dddd"),false);
});

test( "testValidateAdminPassword", function() {
    expect(9);
    equal(true, $.validator.methods['validate-admin-password'].call(this, ""));
    equal(false, $.validator.methods['validate-admin-password'].call(this, null));
    equal(false, $.validator.methods['validate-admin-password'].call(this, undefined));
    equal(true, $.validator.methods['validate-admin-password'].call(this, "   "));
    equal(true, $.validator.methods['validate-admin-password'].call(this, "123@123.com"));
    equal(false, $.validator.methods['validate-admin-password'].call(this, "abc"));
    equal(false, $.validator.methods['validate-admin-password'].call(this, "abc       "));
    equal(false, $.validator.methods['validate-admin-password'].call(this, "     abc      "));
    equal(false, $.validator.methods['validate-admin-password'].call(this, "dddd"));
});

test( "testValidateUrl", function() {
    expect(8);
    equal(true, $.validator.methods['validate-url'].call(this, ""));
    equal(true, $.validator.methods['validate-url'].call(this, null));
    equal(true, $.validator.methods['validate-url'].call(this, undefined));
    equal(false, $.validator.methods['validate-url'].call(this, "   "));
    equal(true, $.validator.methods['validate-url'].call(this, "http://www.google.com"));
    equal(true, $.validator.methods['validate-url'].call(this, "http://127.0.0.1:8080/index.php"));
    equal(true, $.validator.methods['validate-url'].call(this, "http://app-spot.com/index.php"));
    equal(true, $.validator.methods['validate-url'].call(this, "http://app-spot_space.com/index.php"));
});

test( "testValidateCleanUrl", function() {
    expect(8);
    equal(true, $.validator.methods['validate-clean-url'].call(this, ""));
    equal(true, $.validator.methods['validate-clean-url'].call(this, null));
    equal(true, $.validator.methods['validate-clean-url'].call(this, undefined));
    equal(false, $.validator.methods['validate-clean-url'].call(this, "   "));
    equal(true, $.validator.methods['validate-clean-url'].call(this, "http://www.google.com"));
    equal(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1:8080/index.php"));
    equal(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1:8080"));
    equal(false, $.validator.methods['validate-clean-url'].call(this, "http://127.0.0.1"));
});

test( "testValidateXmlIdentifier", function() {
    expect(8);
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, ""));
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, null));
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, undefined));
    equal(false, $.validator.methods['validate-xml-identifier'].call(this, "   "));
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, "abc"));
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, "abc_123"));
    equal(true, $.validator.methods['validate-xml-identifier'].call(this, "abc-123"));
    equal(false, $.validator.methods['validate-xml-identifier'].call(this, "123-abc"));
});

test( "testValidateSsn", function() {
    expect(8);
    equal(true, $.validator.methods['validate-ssn'].call(this, ""));
    equal(true, $.validator.methods['validate-ssn'].call(this, null));
    equal(true, $.validator.methods['validate-ssn'].call(this, undefined));
    equal(false, $.validator.methods['validate-ssn'].call(this, "   "));
    equal(false, $.validator.methods['validate-ssn'].call(this, "abc"));
    equal(true, $.validator.methods['validate-ssn'].call(this, "123-13-1234"));
    equal(true, $.validator.methods['validate-ssn'].call(this, "012-12-1234"));
    equal(false, $.validator.methods['validate-ssn'].call(this, "23-12-1234"));
});

test( "testValidateZip", function() {
    expect(8);
    equal(true, $.validator.methods['validate-zip-us'].call(this, ""));
    equal(true, $.validator.methods['validate-zip-us'].call(this, null));
    equal(true, $.validator.methods['validate-zip-us'].call(this, undefined));
    equal(false, $.validator.methods['validate-zip-us'].call(this, "   "));
    equal(true, $.validator.methods['validate-zip-us'].call(this, "12345-1234"));
    equal(true, $.validator.methods['validate-zip-us'].call(this, "02345"));
    equal(false, $.validator.methods['validate-zip-us'].call(this, "1234"));
    equal(false, $.validator.methods['validate-zip-us'].call(this, "1234-1234"));
});

test( "testValidateDateAu", function() {
    expect(8);
    equal(true, $.validator.methods['validate-date-au'].call(this, ""));
    equal(true, $.validator.methods['validate-date-au'].call(this, null));
    equal(true, $.validator.methods['validate-date-au'].call(this, undefined));
    equal(false, $.validator.methods['validate-date-au'].call(this, "   "));
    equal(true, $.validator.methods['validate-date-au'].call(this, "01/01/2012"));
    equal(true, $.validator.methods['validate-date-au'].call(this, "30/01/2012"));
    equal(false, $.validator.methods['validate-date-au'].call(this, "01/30/2012"));
    equal(false, $.validator.methods['validate-date-au'].call(this, "1/1/2012"));
});

test( "testValidateCurrencyDollar", function() {
    expect(8);
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, ""));
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, null));
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, undefined));
    equal(false, $.validator.methods['validate-currency-dollar'].call(this, "   "));
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, "$123"));
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, "$1,123.00"));
    equal(true, $.validator.methods['validate-currency-dollar'].call(this, "$1234"));
    equal(false, $.validator.methods['validate-currency-dollar'].call(this, "$1234.1234"));
});

test( "testValidateNotNegativeNumber", function() {
    expect(11);
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, ""));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, null));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, undefined));
    equal(false, $.validator.methods['validate-not-negative-number'].call(this, "   "));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, "0"));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, "1"));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, "1234"));
    equal(true, $.validator.methods['validate-not-negative-number'].call(this, "1,234.1234"));
    equal(false, $.validator.methods['validate-not-negative-number'].call(this, "-1"));
    equal(false, $.validator.methods['validate-not-negative-number'].call(this, "-1e"));
    equal(false, $.validator.methods['validate-not-negative-number'].call(this, "-1,234.1234"));
});

test( "testValidateGreaterThanZero", function() {
    expect(11);
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, ""));
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, null));
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, undefined));
    equal(false, $.validator.methods['validate-greater-than-zero'].call(this, "   "));
    equal(false, $.validator.methods['validate-greater-than-zero'].call(this, "0"));
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, "1"));
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, "1234"));
    equal(true, $.validator.methods['validate-greater-than-zero'].call(this, "1,234.1234"));
    equal(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1"));
    equal(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1e"));
    equal(false, $.validator.methods['validate-greater-than-zero'].call(this, "-1,234.1234"));
});

test( "testValidateCssLength", function() {
    expect(11);
    equal(true, $.validator.methods['validate-css-length'].call(this, ""));
    equal(true, $.validator.methods['validate-css-length'].call(this, null));
    equal(true, $.validator.methods['validate-css-length'].call(this, undefined));
    equal(false, $.validator.methods['validate-css-length'].call(this, "   "));
    equal(false, $.validator.methods['validate-css-length'].call(this, "0"));
    equal(true, $.validator.methods['validate-css-length'].call(this, "1"));
    equal(true, $.validator.methods['validate-css-length'].call(this, "1234"));
    equal(true, $.validator.methods['validate-css-length'].call(this, "1,234.1234"));
    equal(false, $.validator.methods['validate-css-length'].call(this, "-1"));
    equal(false, $.validator.methods['validate-css-length'].call(this, "-1e"));
    equal(false, $.validator.methods['validate-css-length'].call(this, "-1,234.1234"));
});

test( "testValidateData", function() {
    expect(9);
    equal(true, $.validator.methods['validate-data'].call(this, ""));
    equal(true, $.validator.methods['validate-data'].call(this, null));
    equal(true, $.validator.methods['validate-data'].call(this, undefined));
    equal(false, $.validator.methods['validate-data'].call(this, "   "));
    equal(false, $.validator.methods['validate-data'].call(this, "123abc"));
    equal(true, $.validator.methods['validate-data'].call(this, "abc"));
    equal(false, $.validator.methods['validate-data'].call(this, " abc"));
    equal(true, $.validator.methods['validate-data'].call(this, "abc123"));
    equal(false, $.validator.methods['validate-data'].call(this, "abc-123"));
});


test( "testValidateOneRequiredByName", function() {
    expect(4);
    var radio = $('<input type="radio" name="radio"/>');
    radio.appendTo("#qunit-fixture");
    ok(!$.validator.methods['validate-one-required-by-name'].call(this,
        null, radio[0]));
    var radio2 = $('<input type="radio" name="radio" checked/>');
    radio2.appendTo("#qunit-fixture");
    ok($.validator.methods['validate-one-required-by-name'].call(this,
        null, radio2[0]));

    var checkbox = $('<input type="checkbox" name="checkbox"/>');
    checkbox.appendTo("#qunit-fixture");
    ok(!$.validator.methods['validate-one-required-by-name'].call(this,
        null, checkbox[0]));
    var checkbox2 = $('<input type="checkbox" name="checkbox" checked/>');
    checkbox2.appendTo("#qunit-fixture");
    ok($.validator.methods['validate-one-required-by-name'].call(this,
        null, checkbox2[0]));
});

test( "testLessThanEqualsTo", function() {
    expect(5);
    var elm1 =  $('<input type="text" value=6 id="element1" />');
    var elm2 =  $('<input type="text" value=5 id="element2" />');
    ok(!$.validator.methods['less-than-equals-to'].call(this, elm1[0].value,
        elm1, elm2));
    elm1[0].value = 4;
    ok($.validator.methods['less-than-equals-to'].call(this, elm1[0].value,
        elm1, elm2));

    var elm3 =  $('<input type="text" id="element3" />');
    var elm4=  $('<input type="text" value=5 id="element4" />');
    ok($.validator.methods['less-than-equals-to'].call(this, elm3[0].value,
        elm3, elm4));

    var elm5 =  $('<input type="text" id="element6" />');
    var elm6=  $('<input type="text" value=6 id="element5" />');
    ok($.validator.methods['less-than-equals-to'].call(this, elm5[0].value,
        elm5, elm6));

    var elm7 =  $('<input type="text" value=20 id="element7" />');
    var elm8=  $('<input type="text" value=100 id="element8" />');
    ok($.validator.methods['less-than-equals-to'].call(this, elm7[0].value,
        elm7, elm8));
});

test( "testGreaterThanEqualsTo", function() {
    expect(5);

    var elm1 =  $('<input type="text" value=6 id="element1" />');
    var elm2 =  $('<input type="text" value=7 id="element2" />');
    ok(!$.validator.methods['greater-than-equals-to'].call(this, elm1[0].value,
        elm1, elm2));
    elm1[0].value = 9;
    ok($.validator.methods['greater-than-equals-to'].call(this, elm1[0].value,
        elm1, elm2));

    var elm3 =  $('<input type="text" id="element3" />');
    var elm4=  $('<input type="text" value=5 id="element4" />');
    ok($.validator.methods['greater-than-equals-to'].call(this, elm3[0].value,
        elm3, elm4));

    var elm5 =  $('<input type="text" id="element6" />');
    var elm6=  $('<input type="text" value=6 id="element5" />');
    ok($.validator.methods['greater-than-equals-to'].call(this, elm5[0].value,
        elm5, elm6));

    var elm7 =  $('<input type="text" value=100 id="element7" />');
    var elm8=  $('<input type="text" value=20 id="element8" />');
    ok($.validator.methods['greater-than-equals-to'].call(this, elm7[0].value,
        elm7, elm8));
});

test( "testValidateGroupedQty", function() {
    expect(5);
    var div1 = $('<div id="div1"/>');
    $('<input type="text"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
                             .appendTo(div1);
    $('<input type="text"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
                             .appendTo(div1);
    $('<input type="text"/>').appendTo(div1);

    ok(!$.validator.methods['validate-grouped-qty'].call(this, null, null, div1[0]));

    var div2 = $('<div id="div2"/>');
    $('<input type="text"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div2);
    $('<input type="text" value="a"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div2);
    $('<input type="text"/>').appendTo(div2);
    ok(!$.validator.methods['validate-grouped-qty'].call(this, null, null, div2[0]));

    var div3 = $('<div id="div3"/>');
    $('<input type="text"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div3);
    $('<input type="text" value="-6"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div3);
    $('<input type="text"/>').appendTo(div3);
    ok(!$.validator.methods['validate-grouped-qty'].call(this, null, null, div3[0]));

    var div4 = $('<div id="div4"/>');
    $('<input type="text"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div4);
    $('<input type="text" value="6"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div4);
    $('<input type="text"/>').appendTo(div4);
    ok($.validator.methods['validate-grouped-qty'].call(this, null, null, div4[0]));

    var div5 = $('<div id="div5"/>');
    $('<input type="text" value=""/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div5);
    $('<input type="text" value="6"/>').attr("data-validate","{'validate-grouped-qty':'#super-product-table'}")
        .appendTo(div5);
    $('<input type="text"/>').appendTo(div5);
    ok($.validator.methods['validate-grouped-qty'].call(this, null, null, div5[0]));

});

test( "testValidateCCTypeSelect", function() {
    expect(14);
     var visaValid = $('<input id="visa-valid" type="text" value="4916808263499650"/>');
     var visaInvalid = $('<input id="visa-invalid" type="text" value="1234567890123456"/>');
     var mcValid = $('<input id="mc-valid" type="text" value="5203731841177490"/>');
     var mcInvalid = $('<input id="mc-invalid" type="text" value="1111222233334444"/>');
     var aeValid = $('<input id="ae-valid" type="text" value="376244899619217"/>');
     var aeInvalid = $('<input id="ae-invalid" type="text" value="123451234512345"/>');

    var diValid = $('<input id="di-valid" type="text" value="6011050000000009"/>');
    var diInvalid = $('<input id="di-invalid" type="text" value="6011199900000005"/>');
    var dnValid = $('<input id="dn-valid" type="text" value="3095434000000001"/>');
    var dnInvalid = $('<input id="dn-invalid" type="text" value="3799999900000003"/>');
    var jcbValid = $('<input id="jcb-valid" type="text" value="3528000000000007"/>');
    var jcbInvalid = $('<input id="jcb-invalid" type="text" value="359000001111118"/>');
    var upValid = $('<input id="up-valid" type="text" value="6221260000000000"/>');
    var upInvalid = $('<input id="up-invalid" type="text" value="6229260000000002"/>');

    ok($.validator.methods['validate-cc-type-select'].call(this, 'VI', null, visaValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'VI', null, visaInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'MC', null, mcValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'MC', null, mcInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'AE', null, aeValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'AE', null, aeInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'DI', null, diValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'DI', null, diInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'DN', null, dnValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'DN', null, dnInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'JCB', null, jcbValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'JCB', null, jcbInvalid));
    ok($.validator.methods['validate-cc-type-select'].call(this, 'UP', null, upValid));
    ok(!$.validator.methods['validate-cc-type-select'].call(this, 'UP', null, upInvalid));
});

test( "testValidateCCNumber", function() {
    expect(37);
    ok($.validator.methods['validate-cc-number'].call(this, '4916835098995909', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '5265071363284878', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6011120623356953', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '371293266574617', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '4916835098995901', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '5265071363284870', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6011120623356951', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '371293266574619', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '2221220000000003', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '2721220000000008', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '601109020000000003', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6011111144444444', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6011222233334444', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6011522233334447', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '601174455555553', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6011745555555550', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '601177455555556', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '601182455555556', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '601187999555558', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '601287999555556', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6444444444444443', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6644444444444441', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '3044444444444444', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '3064444444444449', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '3095444444444442', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '3096444444444441', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '3696444444444445', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '3796444444444444', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '3896444444444443', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '3528444444444449', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '3529444444444448', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6221262244444440', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6229981111111111', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6249981111111117', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6279981111111110', null, null));
    ok($.validator.methods['validate-cc-number'].call(this, '6282981111111115', null, null));
    ok(!$.validator.methods['validate-cc-number'].call(this, '6289981111111118', null, null));
});

test( "testValidateCCType", function() {
    expect(14);
    var select = $('<select id="cc-type">' +
        '<option value="">' +
        '</option><option value="VI">' +
        '</option><option value="MC">' +
        '</option><option value="AE">' +
        '</option><option value="DI">' +
        '</option><option value="DN">' +
        '</option><option value="JCB">' +
        '</option><option value="UP">' +
        '</option>' +
        '</select>');

    select.val('VI');
    ok($.validator.methods['validate-cc-type'].call(this, '4916835098995909', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, select));
    select.val('MC');
    ok($.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '4916835098995909', null, select));
    select.val('AE');
    ok($.validator.methods['validate-cc-type'].call(this, '371293266574617', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '5265071363284878', null, select));
    select.val('DI');
    ok($.validator.methods['validate-cc-type'].call(this, '6011050000000009', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '371293266574617', null, select));
    select.val('DN');
    ok($.validator.methods['validate-cc-type'].call(this, '3095434000000001', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '6011050000000009', null, select));
    select.val('JCB');
    ok($.validator.methods['validate-cc-type'].call(this, '3528000000000007', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '3095434000000001', null, select));
    select.val('UP');
    ok($.validator.methods['validate-cc-type'].call(this, '6221260000000000', null, select));
    ok(!$.validator.methods['validate-cc-type'].call(this, '3528000000000007', null, select));
});

test( "testValidateCCExp", function() {
    expect(3);
    var year = $('<input id="year" type="text" value="4916808263499650"/>'),
        currentTime  = new Date(),
        currentMonth = currentTime.getMonth() + 1,
        currentYear  = currentTime.getFullYear();
    year.val(currentYear);
    if (currentMonth > 1) {
        ok(!$.validator.methods['validate-cc-exp'].call(this, currentMonth - 1, null, year));
    }
    ok($.validator.methods['validate-cc-exp'].call(this, currentMonth, null, year));
    year.val(currentYear + 1);
    ok($.validator.methods['validate-cc-exp'].call(this, currentMonth, null, year));

});

test( "testValidateCCCvn", function() {
    expect(8);
     var ccType = $('<select id="cc-type">'+
     '<option value=""></option>'+
     '<option value="VI"></option>'+
     '<option value="MC"></option>'+
     '<option value="AE"></option>'+
     '<option value="DI"></option>'+
     '</select>');

    ccType.val('VI');
    ok($.validator.methods['validate-cc-cvn'].call(this, '123', null, ccType));
    ok(!$.validator.methods['validate-cc-cvn'].call(this, '1234', null, ccType));
    ccType.val('MC');
    ok($.validator.methods['validate-cc-cvn'].call(this, '123', null, ccType));
    ok(!$.validator.methods['validate-cc-cvn'].call(this, '1234', null, ccType));
    ccType.val('AE');
    ok($.validator.methods['validate-cc-cvn'].call(this, '1234', null, ccType));
    ok(!$.validator.methods['validate-cc-cvn'].call(this, '123', null, ccType));
    ccType.val('DI');
    ok($.validator.methods['validate-cc-cvn'].call(this, '123', null, ccType));
    ok(!$.validator.methods['validate-cc-cvn'].call(this, '1234', null, ccType));
});

test( "testValidateNumberRange", function() {
    expect(14);
    ok($.validator.methods['validate-number-range'].call(this, '-1', null, null));
    ok($.validator.methods['validate-number-range'].call(this, '1', null, null));
    ok($.validator.methods['validate-number-range'].call(this, '', null, null));
    ok($.validator.methods['validate-number-range'].call(this, null, null, null));
    ok($.validator.methods['validate-number-range'].call(this, '0', null, null));
    ok(!$.validator.methods['validate-number-range'].call(this, 'asds', null, null));

    ok($.validator.methods['validate-number-range'].call(this, '10', null, '10-20.06'));
    ok($.validator.methods['validate-number-range'].call(this, '15', null, '10-20.06'));
    ok(!$.validator.methods['validate-number-range'].call(this, '1', null, '10-20.06'));
    ok(!$.validator.methods['validate-number-range'].call(this, '30', null, '10-20.06'));

    var el1 = $('<input type="text" value="" class="validate-number-range number-range-10-20 number-range-10-100.20">').get(0);
    ok($.validator.methods['validate-number-range'].call(this, '10', el1, null));
    ok($.validator.methods['validate-number-range'].call(this, '15', el1, null));
    ok(!$.validator.methods['validate-number-range'].call(this, '1', el1, null));
    ok($.validator.methods['validate-number-range'].call(this, '30', el1, null));
});



test( "testValidateDigitsRange", function() {
    expect(15);
    ok($.validator.methods['validate-digits-range'].call(this, '-1', null, null));
    ok($.validator.methods['validate-digits-range'].call(this, '1', null, null));
    ok($.validator.methods['validate-digits-range'].call(this, '', null, null));
    ok($.validator.methods['validate-digits-range'].call(this, null, null, null));
    ok($.validator.methods['validate-digits-range'].call(this, '0', null, null));
    ok(!$.validator.methods['validate-digits-range'].call(this, 'asds', null, null));

    ok($.validator.methods['validate-digits-range'].call(this, '10', null, '10-20'));
    ok($.validator.methods['validate-digits-range'].call(this, '15', null, '10-20'));
    ok(!$.validator.methods['validate-digits-range'].call(this, '1', null, '10-20'));
    ok(!$.validator.methods['validate-digits-range'].call(this, '30', null, '10-20'));
    ok($.validator.methods['validate-digits-range'].call(this, '30', null, '10-20.06'));

    var el1 = $('<input type="text" value="" class="validate-digits-range digits-range-10-20 digits-range-10-100.20">').get(0);
    ok($.validator.methods['validate-digits-range'].call(this, '10', el1, null));
    ok($.validator.methods['validate-digits-range'].call(this, '15', el1, null));
    ok(!$.validator.methods['validate-digits-range'].call(this, '1', el1, null));
    ok(!$.validator.methods['validate-digits-range'].call(this, '30', el1, null));
});
