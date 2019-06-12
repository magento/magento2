/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /*eslint max-nested-callbacks: ["error", 5]*/
    describe('Signifyd device fingerprint client script', function () {

        it('SIGNIFYD_GLOBAL object initialization check', function (done) {
            var script = document.createElement('script');

            script.setAttribute('src', 'https://cdn-scripts.signifyd.com/api/script-tag.js');
            script.setAttribute('id', 'sig-api');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('async', '');
            script.setAttribute('data-order-session-id', 'mage-jasmin-test');

            $(document.body).append(script);

            setTimeout(function () {
                var signifyd = window.SIGNIFYD_GLOBAL;

                expect(signifyd).toBeDefined();
                expect(typeof signifyd).toBe('object');
                expect(signifyd.scriptTagHasLoaded).toBeDefined();
                expect(typeof signifyd.scriptTagHasLoaded).toBe('function');
                expect(signifyd.scriptTagHasLoaded()).toBe(true);
                done();
            }, 10000);

        }, 12000);
    });
});
