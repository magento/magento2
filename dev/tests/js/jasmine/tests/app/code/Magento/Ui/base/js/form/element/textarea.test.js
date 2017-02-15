/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/textarea'
], function (TextareaElement) {
    'use strict';

    describe('Magento_Ui/js/form/element/textarea', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'textarea'
            };
            model = new TextareaElement(params);
        });

        it('check if component defined', function () {
            expect(model).toBeDefined();
        });
    });
});
