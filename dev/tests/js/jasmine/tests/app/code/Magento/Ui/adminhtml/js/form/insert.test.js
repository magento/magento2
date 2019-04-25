/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_Ui/js/form/components/insert'
], function ($, Insert) {
    'use strict';

    describe('Magento_Ui/js/form/components/insert', function () {
        var obj, params;

        beforeEach(function () {
            params = {
                isRendered: false,
                autoRender: false
            };
            obj = new Insert(params);
        });

        describe('"onRender" method', function () {
            it('Check method call with not JSON response', function () {
                var data = '<Not JSON>';

                obj.onRender(data);

                expect(obj.content()).toBe(data);
                expect(obj.isRendered).toBeTruthy();
                expect(obj.startRender).toBeFalsy();
            });

            it('Check method call with ajaxExpired JSON', function () {
                var data = '{"ajaxExpired": 1, "ajaxRedirect": "#test"}';

                obj.onRender(data);

                expect(obj.content()).toBe('');
                expect(window.location.href).toContain('#test');
            });
        });
    });
});
