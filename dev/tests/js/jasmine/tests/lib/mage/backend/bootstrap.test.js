/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* global jQuery */
define([
    'jquery',
    'squire',
    'mage/backend/notification'
], function ($, Squire) {
    'use strict';

    var injector = new Squire();

    describe('mage/backend/bootstrap', function () {
        beforeEach(function (done) {
            injector.require(['mage/backend/bootstrap'], function () {
                done();
            });
        });
        describe('"sendPostponeRequest" method', function () {
            it('should insert "Error" notification if request failed', function () {
                jQuery('<div class="page-main-actions"></div>').appendTo('body');
                jQuery('body').notification();

                jQuery.ajax().abort();

                expect(jQuery('.message-error').length).toBe(1);
                expect(jQuery('body:contains("A technical problem with the server created an error")').length).toBe(1);
            });
        });
    });
});
