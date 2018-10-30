/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    describe('Testing credit card validation', function () {
        it('Card type matches credit card number', function () {
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'VI', null, $('<input id="visa-valid" type="text" value="4916808263499650"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'VI', null, $('<input id="visa-invalid" type="text" value="1234567890123456"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'MC', null, $('<input id="mc-valid" type="text" value="5203731841177490"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'MC', null, $('<input id="mc-invalid" type="text" value="1111222233334444"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'MC', null, $('<input id="mc-valid" type="text" value="2221220000000003"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'MC', null, $('<input id="mc-invalid" type="text" value="2721220000000008"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'AE', null, $('<input id="ae-valid" type="text" value="376244899619217"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'AE', null, $('<input id="ae-invalid" type="text" value="123451234512345"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="601109020000000003"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="6011111144444444"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="6011222233334444"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="6011522233334447"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="601174455555553"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="6011755555555550"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="601177455555556"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="601182455555556"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="601187999555558"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="601287999555556"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-valid" type="text" value="6444444444444443"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DI', null, $('<input id="di-invalid" type="text" value="6644444444444441"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3095434000000001"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-invalid" type="text" value="3799999900000003"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3044444444444444"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-invalid" type="text" value="3064444444444449"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3095444444444442"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-invalid" type="text" value="3096444444444441"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3696444444444445"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-invalid" type="text" value="3796444444444444"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3696444444444445"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-valid" type="text" value="3896444444444443"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'DN', null, $('<input id="dn-invalid" type="text" value="3796444444444444"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'JCB', null, $('<input id="jcb-valid" type="text" value="3528444444444449"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'JCB', null, $('<input id="jcb-invalid" type="text" value="3527444444444448"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'JCB', null, $('<input id="jcb-invalid" type="text" value="3590444444444448"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-valid" type="text" value="6221262244444440"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-invalid" type="text" value="6229981111111111"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-valid" type="text" value="6249981111111117"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-invalid" type="text" value="6279981111111110"/>'))
            ).toEqual(false);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-valid" type="text" value="6282981111111115"/>'))
            ).toEqual(true);
            expect($.validator.methods['validate-cc-type-select'].call(
                this, 'UN', null, $('<input id="un-invalid" type="text" value="6289981111111118"/>'))
            ).toEqual(false);
        });
    });

    describe('Testing UK Mobile number validation', function () {
        it('Valid UK Mobile Number', function () {
            var element = $('<input id="telephone" type="text" value="07400123456" />');

            expect($.validator.methods.mobileUK.call(
                $.validator.prototype, element.val(), element.get(0)
            )).toBeTruthy();
        });
        it('Invalid UK Mobile Number', function () {
            var element = $('<input id="telephone" type="text" value="06400123456" />');

            expect($.validator.methods.mobileUK.call(
                $.validator.prototype, element.val(), element.get(0)
            )).toBeFalsy();
        });
        it('Valid UK Mobile Number (International)', function () {
            var element = $('<input id="telephone" type="text" value="+447400123456" />');

            expect($.validator.methods.mobileUK.call(
                $.validator.prototype, element.val(), element.get(0)
            )).toBeTruthy();
        });
        it('Invalid UK Mobile Number', function () {
            var element = $('<input id="telephone" type="text" value="+446400123456" />');

            expect($.validator.methods.mobileUK.call(
                $.validator.prototype, element.val(), element.get(0)
            )).toBeFalsy();
        });
    });

    describe('Validation of the password against the user name', function () {
        it('rejects data, if password is the same as user name', function () {
            var password = $('<input id="password" type="password" value="EmailPasswordTheSame" />'),
                email = $('<input id="email" type="email" value="EmailPasswordTheSame" />');

            expect($.validator.methods['password-not-equal-to-user-name'].call(
                $.validator.prototype, password.val(), null, email.val()
            )).toEqual(false);
        });

        it('approves data, if password is different from user name', function () {
            var password = $('<input id="password" type="password" value="SomePassword" />'),
                email = $('<input id="email" type="email" value="SomeEmail" />');

            expect($.validator.methods['password-not-equal-to-user-name'].call(
                $.validator.prototype, password.val(), null, email.val()
            )).toEqual(true);
        });
    });

    describe('Testing 3 bytes characters only policy (UTF-8)', function () {
        it('rejects data, if any of the characters cannot be stored using UTF-8 collation', function () {
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '😅😂', null
            )).toEqual(false);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '😅 test 😂', null
            )).toEqual(false);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '💩 👻 💀', null
            )).toEqual(false);
        });

        it('approves data, if all the characters can be stored using UTF-8 collation', function () {
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '!$-_%ç&#?!', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '1234567890', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '   ', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'test', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'испытание', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'тест', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'փորձարկում', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'परीक्षण', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'テスト', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '테스트', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '测试', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, '測試', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'ทดสอบ', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'δοκιμή', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'اختبار', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'تست', null
            )).toEqual(true);
            expect($.validator.methods['validate-no-utf8mb4-characters'].call(
                $.validator.prototype, 'מִבְחָן', null
            )).toEqual(true);
        });
    });

    describe('Testing validate-no-html-tags', function () {
        it('validate-no-html-tags', function () {
            expect($.validator.methods['validate-no-html-tags']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-no-html-tags']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-no-html-tags']
                .call($.validator.prototype, 'abc')).toEqual(true);
            expect($.validator.methods['validate-no-html-tags']
                .call($.validator.prototype, '<div>abc</div>')).toEqual(false);
        });
    });

    describe('Testing allow-container-className', function () {
        it('allow-container-className', function () {
            var radio = $('<input type="radio" class="change-container-classname"/>'),
                checkbox = $('<input type="checkbox" class="change-container-classname"/>'),
                radio2 = $('<input type="radio"/>'),
                checkbox2 = $('<input type="checkbox"/>');

            expect($.validator.methods['allow-container-className']
                .call($.validator.prototype, radio[0])).toEqual(true);
            expect($.validator.methods['allow-container-className']
                .call($.validator.prototype, checkbox[0])).toEqual(true);
            expect($.validator.methods['allow-container-className']
                .call($.validator.prototype, radio2[0])).toEqual(false);
            expect($.validator.methods['allow-container-className']
                .call($.validator.prototype, checkbox2[0])).toEqual(false);
        });
    });

    describe('Testing validate-select', function () {
        it('validate-select', function () {
            expect($.validator.methods['validate-select']
                .call($.validator.prototype, '')).toEqual(false);
            expect($.validator.methods['validate-select']
                .call($.validator.prototype, 'none')).toEqual(false);
            expect($.validator.methods['validate-select']
                .call($.validator.prototype, null)).toEqual(false);
            expect($.validator.methods['validate-select']
                .call($.validator.prototype, undefined)).toEqual(false);
            expect($.validator.methods['validate-select']
                .call($.validator.prototype, 'abc')).toEqual(true);
        });
    });

    describe('Testing validate-no-empty', function () {
        it('validate-no-empty', function () {
            expect($.validator.methods['validate-no-empty']
                .call($.validator.prototype, '')).toEqual(false);
            expect($.validator.methods['validate-no-empty']
                .call($.validator.prototype, null)).toEqual(false);
            expect($.validator.methods['validate-no-empty']
                .call($.validator.prototype, undefined)).toEqual(false);
            expect($.validator.methods['validate-no-empty']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-no-empty']
                .call($.validator.prototype, 'test')).toEqual(true);
        });
    });

    describe('Testing validate-alphanum-with-spaces', function () {
        it('validate-alphanum-with-spaces', function () {
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, '   ')).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, 'abc   ')).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, ' 123  ')).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, '  abc123 ')).toEqual(true);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, '  !@# ')).toEqual(false);
            expect($.validator.methods['validate-alphanum-with-spaces']
                .call($.validator.prototype, '  abc.123 ')).toEqual(false);
        });
    });

    describe('Testing validate-phoneStrict', function () {
        it('validate-phoneStrict', function () {
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '5121231234')).toEqual(false);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '512.123.1234')).toEqual(false);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '512-123-1234')).toEqual(true);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '(512)123-1234')).toEqual(true);
            expect($.validator.methods['validate-phoneStrict']
                .call($.validator.prototype, '(512) 123-1234')).toEqual(true);
        });
    });

    describe('Testing validate-phoneLax', function () {
        it('validate-phoneLax', function () {
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '5121231234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '512.123.1234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '512-123-1234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '(512)123-1234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '(512) 123-1234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '(512)1231234')).toEqual(true);
            expect($.validator.methods['validate-phoneLax']
                .call($.validator.prototype, '(512)_123_1234')).toEqual(false);
        });
    });

    describe('Testing validate-fax', function () {
        it('validate-fax', function () {
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '5121231234')).toEqual(false);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '512.123.1234')).toEqual(false);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '512-123-1234')).toEqual(true);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '(512)123-1234')).toEqual(true);
            expect($.validator.methods['validate-fax']
                .call($.validator.prototype, '(512) 123-1234')).toEqual(true);
        });
    });

    describe('Testing validate-email', function () {
        it('validate-email', function () {
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, '123@123.com')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc@124.en')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc.abc.abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc.abc-abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc.abc_abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-email']
                .call($.validator.prototype, 'abc.abc_abc@abc')).toEqual(false);
        });
    });

    describe('Testing validate-emailSender', function () {
        it('validate-emailSender', function () {
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, '   ')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, '123@123.com')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, 'abc@124.en')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, 'abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, 'abc.abc.abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, 'abc.abc-abc@abc.commmmm')).toEqual(true);
            expect($.validator.methods['validate-emailSender']
                .call($.validator.prototype, 'abc.abc_abc@abc.commmmm')).toEqual(true);
        });
    });

    describe('Testing validate-password', function () {
        it('validate-password', function () {
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, null)).toEqual(false);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, undefined)).toEqual(false);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, '   ')).toEqual(true);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, '123@123.com')).toEqual(true);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, 'abc')).toEqual(false);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, 'abc       ')).toEqual(false);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, '     abc      ')).toEqual(false);
            expect($.validator.methods['validate-password']
                .call($.validator.prototype, 'dddd')).toEqual(false);
        });
    });

    describe('Testing validate-admin-password', function () {
        it('validate-admin-password', function () {
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, null)).toEqual(false);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, undefined)).toEqual(false);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, '   ')).toEqual(true);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, '123@123.com')).toEqual(true);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, 'abc')).toEqual(false);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, 'abc       ')).toEqual(false);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, '     abc      ')).toEqual(false);
            expect($.validator.methods['validate-admin-password']
                .call($.validator.prototype, 'dddd')).toEqual(false);
        });
    });

    describe('Testing validate-url', function () {
        it('validate-url', function () {
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, 'http://www.google.com')).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, 'http://127.0.0.1:8080/index.php')).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, 'http://app-spot.com/index.php')).toEqual(true);
            expect($.validator.methods['validate-url']
                .call($.validator.prototype, 'http://app-spot_space.com/index.php')).toEqual(true);
        });
    });

    describe('Testing validate-clean-url', function () {
        it('validate-clean-url', function () {
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, 'http://www.google.com')).toEqual(true);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, 'http://127.0.0.1:8080/index.php')).toEqual(false);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, 'http://127.0.0.1:8080')).toEqual(false);
            expect($.validator.methods['validate-clean-url']
                .call($.validator.prototype, 'http://127.0.0.1')).toEqual(false);
        });
    });

    describe('Testing validate-xml-identifier', function () {
        it('validate-xml-identifier', function () {
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, 'abc')).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, 'abc_123')).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, 'abc-123')).toEqual(true);
            expect($.validator.methods['validate-xml-identifier']
                .call($.validator.prototype, '123-abc')).toEqual(false);
        });
    });

    describe('Testing validate-ssn', function () {
        it('validate-ssn', function () {
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, 'abc')).toEqual(false);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, '123-13-1234')).toEqual(true);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, '012-12-1234')).toEqual(true);
            expect($.validator.methods['validate-ssn']
                .call($.validator.prototype, '23-12-1234')).toEqual(false);
        });
    });

    describe('Testing validate-zip-us', function () {
        it('validate-zip-us', function () {
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '12345-1234')).toEqual(true);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '02345')).toEqual(true);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '1234')).toEqual(false);
            expect($.validator.methods['validate-zip-us']
                .call($.validator.prototype, '1234-1234')).toEqual(false);
        });
    });

    describe('Testing validate-date-au', function () {
        it('validate-date-au', function () {
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '01/01/2012')).toEqual(true);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '30/01/2012')).toEqual(true);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '01/30/2012')).toEqual(false);
            expect($.validator.methods['validate-date-au']
                .call($.validator.prototype, '1/1/2012')).toEqual(false);
        });
    });

    describe('Testing validate-currency-dollar', function () {
        it('validate-currency-dollar', function () {
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '$123')).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '$1,123.00')).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '$1234')).toEqual(true);
            expect($.validator.methods['validate-currency-dollar']
                .call($.validator.prototype, '$1234.1234')).toEqual(false);
        });
    });

    describe('Testing validate-not-negative-number', function () {
        it('validate-not-negative-number', function () {
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '0')).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '1')).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '1234')).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '1,234.1234')).toEqual(true);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '-1')).toEqual(false);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '-1e')).toEqual(false);
            expect($.validator.methods['validate-not-negative-number']
                .call($.validator.prototype, '-1,234.1234')).toEqual(false);
        });
    });

    describe('Testing validate-greater-than-zero', function () {
        it('validate-greater-than-zero', function () {
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '0')).toEqual(false);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '1')).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '1234')).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '1,234.1234')).toEqual(true);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '-1')).toEqual(false);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '-1e')).toEqual(false);
            expect($.validator.methods['validate-greater-than-zero']
                .call($.validator.prototype, '-1,234.1234')).toEqual(false);
        });
    });

    describe('Testing validate-css-length', function () {
        it('validate-css-length', function () {
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, null)).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, undefined)).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '0')).toEqual(true);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '1')).toEqual(true);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '1234')).toEqual(true);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '1,234.1234')).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '-1')).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '-1e')).toEqual(false);
            expect($.validator.methods['validate-css-length']
                .call($.validator.prototype, '-1,234.1234')).toEqual(false);
        });
    });

    describe('Testing validate-data', function () {
        it('validate-data', function () {
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, '')).toEqual(true);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, null)).toEqual(true);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, undefined)).toEqual(true);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, '   ')).toEqual(false);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, '123abc')).toEqual(false);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, 'abc')).toEqual(true);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, ' abc')).toEqual(false);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, 'abc123')).toEqual(true);
            expect($.validator.methods['validate-data']
                .call($.validator.prototype, 'abc-123')).toEqual(false);
        });
    });

    describe('Testing validate-one-required-by-name', function () {
        it('validate-one-required-by-name', function () {
            var radio = $('<input type="radio" name="radio"/>'),
                radio2 = $('<input type="radio" name="radio" checked/>'),
                checkbox = $('<input type="checkbox" name="checkbox"/>'),
                checkbox2 = $('<input type="checkbox" name="checkbox" checked/>'),
                $test = $('<div id="test-block" />'),
                prevForm = $.validator.prototype.currentForm;

            $.validator.prototype.currentForm = $test[0];

            $test.append(radio);
            expect($.validator.methods['validate-one-required-by-name']
                .call($.validator.prototype, null, radio[0], true)).toEqual(false);
            $test.append(radio2);
            expect($.validator.methods['validate-one-required-by-name']
                .call($.validator.prototype, null, radio2[0], true)).toEqual(true);
            $test.append(checkbox);
            expect($.validator.methods['validate-one-required-by-name']
                .call($.validator.prototype, null, checkbox[0], true)).toEqual(false);
            $test.append(checkbox2);
            expect($.validator.methods['validate-one-required-by-name']
                .call($.validator.prototype, null, checkbox2[0], true)).toEqual(true);

            $.validator.prototype.currentForm = prevForm;
        });
    });

    describe('Testing less-than-equals-to', function () {
        it('less-than-equals-to', function () {
            var elm1 =  $('<input type="text" value="6" id="element1" />'),
                elm2 =  $('<input type="text" value="5" id="element2" />'),
                elm3 =  $('<input type="text" id="element3" />'),
                elm4 =  $('<input type="text" value=5 id="element4" />'),
                elm5 =  $('<input type="text" id="element6" />'),
                elm6 =  $('<input type="text" value=6 id="element5" />'),
                elm7 =  $('<input type="text" value=20 id="element7" />'),
                elm8 =  $('<input type="text" value=100 id="element8" />');

            expect($.validator.methods['less-than-equals-to']
                .call($.validator.prototype, elm1[0].value, elm1, elm2)).toEqual(false);
            elm1[0].value = 4;
            expect($.validator.methods['less-than-equals-to']
                .call($.validator.prototype, elm1[0].value, elm1, elm2)).toEqual(true);
            expect($.validator.methods['less-than-equals-to']
                .call($.validator.prototype, elm3[0].value, elm3, elm4)).toEqual(true);
            expect($.validator.methods['less-than-equals-to']
                .call($.validator.prototype, elm5[0].value, elm5, elm6)).toEqual(true);
            expect($.validator.methods['less-than-equals-to']
                .call($.validator.prototype, elm7[0].value, elm7, elm8)).toEqual(true);
        });
    });

    describe('Testing greater-than-equals-to', function () {
        it('greater-than-equals-to', function () {
            var elm1 =  $('<input type="text" value=6 id="element1" />'),
                elm2 =  $('<input type="text" value=7 id="element2" />'),
                elm3 =  $('<input type="text" id="element3" />'),
                elm4 =  $('<input type="text" value=5 id="element4" />'),
                elm5 =  $('<input type="text" id="element6" />'),
                elm6 =  $('<input type="text" value=6 id="element5" />'),
                elm7 =  $('<input type="text" value=100 id="element7" />'),
                elm8 =  $('<input type="text" value=20 id="element8" />');

            expect($.validator.methods['greater-than-equals-to']
                .call($.validator.prototype, elm1[0].value, elm1, elm2)).toEqual(false);
            elm1[0].value = 9;
            expect($.validator.methods['greater-than-equals-to']
                .call($.validator.prototype, elm1[0].value, elm1, elm2)).toEqual(true);
            expect($.validator.methods['greater-than-equals-to']
                .call($.validator.prototype, elm3[0].value, elm3, elm4)).toEqual(true);
            expect($.validator.methods['greater-than-equals-to']
                .call($.validator.prototype, elm5[0].value, elm5, elm6)).toEqual(true);
            expect($.validator.methods['greater-than-equals-to']
                .call($.validator.prototype, elm7[0].value, elm7, elm8)).toEqual(true);
        });
    });

    describe('Testing validate-cc-type-select', function () {
        it('validate-cc-type-select', function () {
            var visaValid = $('<input id="visa-valid" type="text" value="4916808263499650"/>'),
                visaInvalid = $('<input id="visa-invalid" type="text" value="1234567890123456"/>'),
                mcValid = $('<input id="mc-valid" type="text" value="5203731841177490"/>'),
                mcInvalid = $('<input id="mc-invalid" type="text" value="1111222233334444"/>'),
                aeValid = $('<input id="ae-valid" type="text" value="376244899619217"/>'),
                aeInvalid = $('<input id="ae-invalid" type="text" value="123451234512345"/>'),
                diValid = $('<input id="di-valid" type="text" value="6011050000000009"/>'),
                diInvalid = $('<input id="di-invalid" type="text" value="6011199900000005"/>'),
                dnValid = $('<input id="dn-valid" type="text" value="3095434000000001"/>'),
                dnInvalid = $('<input id="dn-invalid" type="text" value="3799999900000003"/>'),
                jcbValid = $('<input id="jcb-valid" type="text" value="3528000000000007"/>'),
                jcbInvalid = $('<input id="jcb-invalid" type="text" value="359000001111118"/>');

            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'VI', null, visaValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'VI', null, visaInvalid)).toEqual(false);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'MC', null, mcValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'MC', null, mcInvalid)).toEqual(false);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'AE', null, aeValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'AE', null, aeInvalid)).toEqual(false);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'DI', null, diValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'DI', null, diInvalid)).toEqual(false);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'DN', null, dnValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'DN', null, dnInvalid)).toEqual(false);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'JCB', null, jcbValid)).toEqual(true);
            expect($.validator.methods['validate-cc-type-select']
                .call($.validator.prototype, 'JCB', null, jcbInvalid)).toEqual(false);
        });
    });

    describe('Testing validate-cc-number', function () {
        it('validate-cc-number', function () {
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '4916835098995909', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '5265071363284878', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011120623356953', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '371293266574617', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '4916835098995901', null, null)).toEqual(false);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '5265071363284870', null, null)).toEqual(false);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011120623356951', null, null)).toEqual(false);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '371293266574619', null, null)).toEqual(false);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '2221220000000003', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '2721220000000008', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601109020000000003', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011111144444444', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011222233334444', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011522233334447', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601174455555553', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6011745555555550', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601177455555556', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601182455555556', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601187999555558', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '601287999555556', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6444444444444443', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6644444444444441', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3044444444444444', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3064444444444449', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3095444444444442', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3096444444444441', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3696444444444445', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3796444444444444', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3896444444444443', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3528444444444449', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '3529444444444448', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6221262244444440', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6229981111111111', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6249981111111117', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6279981111111110', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6282981111111115', null, null)).toEqual(true);
            expect($.validator.methods['validate-cc-number']
                .call($.validator.prototype, '6289981111111118', null, null)).toEqual(true);
        });
    });

    describe('Testing validate-cc-type', function () {
        it('validate-cc-type', function () {
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
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '4916835098995909', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '5265071363284878', null, select)).toEqual(false);

            select.val('MC');
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '5265071363284878', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '4916835098995909', null, select)).toEqual(false);

            select.val('AE');
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '371293266574617', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '5265071363284878', null, select)).toEqual(false);

            select.val('DI');
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '6011050000000009', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '371293266574617', null, select)).toEqual(false);

            select.val('DN');
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '3095434000000001', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '6011050000000009', null, select)).toEqual(false);

            select.val('JCB');
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '3528000000000007', null, select)).toEqual(true);
            expect($.validator.methods['validate-cc-type']
                .call($.validator.prototype, '3095434000000001', null, select)).toEqual(false);
        });
    });

    describe('Testing validate-cc-exp', function () {
        it('validate-cc-exp', function () {
            var year = $('<input id="year" type="text" value="4916808263499650"/>'),
                currentTime  = new Date(),
                currentMonth = currentTime.getMonth() + 1,
                currentYear  = currentTime.getFullYear();

            year.val(currentYear);

            if (currentMonth > 1) {
                expect($.validator.methods['validate-cc-exp']
                    .call($.validator.prototype, currentMonth - 1, null, year)).toEqual(false);
            }
            expect($.validator.methods['validate-cc-exp']
                .call($.validator.prototype, currentMonth, null, year)).toEqual(true);
            year.val(currentYear + 1);
            expect($.validator.methods['validate-cc-exp']
                .call($.validator.prototype, currentMonth, null, year)).toEqual(true);
        });
    });

    describe('Testing validate-cc-cvn', function () {
        it('validate-cc-cvn', function () {
            var ccType = $('<select id="cc-type">' +
                '<option value=""></option>' +
                '<option value="VI"></option>' +
                '<option value="MC"></option>' +
                '<option value="AE"></option>' +
                '<option value="DI"></option>' +
                '</select>');

            ccType.val('VI');
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '123', null, ccType)).toEqual(true);
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '1234', null, ccType)).toEqual(false);

            ccType.val('MC');
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '123', null, ccType)).toEqual(true);
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '1234', null, ccType)).toEqual(false);

            ccType.val('AE');
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '1234', null, ccType)).toEqual(true);
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '123', null, ccType)).toEqual(false);

            ccType.val('DI');
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '123', null, ccType)).toEqual(true);
            expect($.validator.methods['validate-cc-cvn']
                .call($.validator.prototype, '1234', null, ccType)).toEqual(false);
        });
    });

    describe('Testing validate-number-range', function () {
        it('validate-number-range', function () {
            var el1 = $('<input type="text" value="" ' +
                'class="validate-number-range number-range-10-20 number-range-10-100.20">').get(0);

            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '-1', null, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '1', null, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '', null, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, null, null, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '0', null, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, 'asds', null, null)).toEqual(false);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '10', null, '10-20.06')).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '15', null, '10-20.06')).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '1', null, '10-20.06')).toEqual(false);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '30', null, '10-20.06')).toEqual(false);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '10', el1, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '15', el1, null)).toEqual(true);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '1', el1, null)).toEqual(false);
            expect($.validator.methods['validate-number-range']
                .call($.validator.prototype, '30', el1, null)).toEqual(true);
        });
    });

    describe('Testing validate-digits-range', function () {
        it('validate-digits-range', function () {
            var el1 = $('<input type="text" value="" ' +
                'class="validate-digits-range digits-range-10-20 digits-range-10-100.20">').get(0);

            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '-1', null, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '1', null, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '', null, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, null, null, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '0', null, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, 'asds', null, null)).toEqual(false);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '10', null, '10-20')).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '15', null, '10-20')).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '1', null, '10-20')).toEqual(false);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '30', null, '10-20')).toEqual(false);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '30', null, '10-20.06')).toEqual(false);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '10', el1, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '15', el1, null)).toEqual(true);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '1', el1, null)).toEqual(false);
            expect($.validator.methods['validate-digits-range']
                .call($.validator.prototype, '30', el1, null)).toEqual(false);
        });
    });

    describe('Testing validate-forbidden-extensions', function () {
        it('validate-forbidden-extensions', function () {
            var el1 = $('<input type="text" value="" ' +
                'class="validate-extensions" data-validation-params="php,phtml">').get(0);

            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'php', el1, null)).toEqual(false);
            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'php,phtml', el1, null)).toEqual(false);
            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'html', el1, null)).toEqual(true);
            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'html,png', el1, null)).toEqual(true);
            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'php,html', el1, null)).toEqual(false);
            expect($.validator.methods['validate-forbidden-extensions']
                .call($.validator.prototype, 'html,php', el1, null)).toEqual(false);
        });
    });
});
