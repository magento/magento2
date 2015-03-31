/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/template',
    'jquery'
], function (mageTemplate, $) {
    'use strict';

    describe('mage/template', function () {
        var templateString  = 'Hello, <%= target %>',
            templateData    = {
                target: 'Magento User'
            },
            expectedString  = 'Hello, Magento User',
            templateNode    = $('<script id="hello" type="text/x-magento-template">' + templateString + '</script>');

        beforeAll(function () {
            templateNode.appendTo(document.body);
        });

        afterAll(function () {
            templateNode.remove();
        });

        it('compiles template function when only selector is passed', function () {
            var template = mageTemplate('#hello');

            expect(typeof template).toBe('function');
            expect(template(templateData)).toEqual(expectedString);
        });

        it('renders template when both selector and data are passed', function () {
            expect(mageTemplate('#hello', templateData)).toEqual(expectedString);
        });

        it('compiles template with string passed only', function () {
            var template = mageTemplate(templateString);

            expect(typeof template).toEqual('function');
            expect(template(templateData)).toEqual(expectedString);
        });

        it('renders template with string and data passed', function () {
            expect(mageTemplate(templateString, templateData)).toEqual(expectedString);
        });
    });
});
