/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
});
