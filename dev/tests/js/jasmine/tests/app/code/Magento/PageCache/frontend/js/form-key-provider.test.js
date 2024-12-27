/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_PageCache/js/form-key-provider'
], function ($, formKeyInit) {
    'use strict';

    describe('Testing FormKey Provider', function () {
        var inputContainer;

        function getFormKeyCookie() {
            var nameEQ = 'form_key=',
                cookieArr = document.cookie.split(';');

            return cookieArr[0].split(nameEQ)[1];
        }

        beforeEach(function () {
            inputContainer =  document.createElement('input');
            inputContainer.setAttribute('value', '');
            inputContainer.setAttribute('name', 'form_key');
            document.querySelector('body').appendChild(inputContainer);
        });

        afterEach(function () {
            $(inputContainer).remove();
            document.cookie = 'form_key= ; expires = Thu, 01 Jan 1970 00:00:00 GMT';
        });

        it('sets value of input[form_key]', function () {
            var expires,
                date = new Date();

            date.setTime(date.getTime() + 86400000);
            expires = '; expires=' + date.toUTCString();
            document.cookie = 'form_key=FAKE_COOKIE' + expires + '; path=/';
            formKeyInit({'isPaginationCacheEnabled': 0});
            expect($(inputContainer).val()).toEqual('FAKE_COOKIE');
        });

        it('widget sets value to input[form_key] in case it empty', function () {
            document.cookie = 'form_key= ; expires = Thu, 01 Jan 1970 00:00:00 GMT';
            formKeyInit({'isPaginationCacheEnabled': 0});
            expect($(inputContainer).val()).toEqual(jasmine.any(String));
            expect($(inputContainer).val().length).toEqual(16);
        });

        it('widget gets value from input[form_key] in case cookie is empty is pagination cache enabled', function () {
            var formKey = 'TestInputStrings';

            document.cookie = 'form_key= ; expires = Thu, 01 Jan 1970 00:00:00 GMT';
            $(inputContainer).val(formKey);
            formKeyInit({'isPaginationCacheEnabled': 1});

            expect($(inputContainer).val()).toEqual(jasmine.any(String));
            expect($(inputContainer).val().length).toEqual(16);
            expect(formKey).toEqual(getFormKeyCookie());
        });
    });
});
