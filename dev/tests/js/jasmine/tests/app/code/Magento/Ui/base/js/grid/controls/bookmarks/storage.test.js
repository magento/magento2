/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/storage'
], function (Storage) {
    'use strict';

    describe('ui/js/grid/controls/bookmarks/storage', function () {
        var storageObj;

        beforeEach(function () {
            storageObj = new Storage();
        });
        it('has setter method', function () {
            spyOn(storageObj, 'set');
            storageObj.set();
            expect(storageObj.set).toHaveBeenCalled();
        });
        it('has getter method', function () {
            spyOn(storageObj, 'get');
            storageObj.get();
            expect(storageObj.get).toHaveBeenCalled();
        });
        it('has remove method', function () {
            spyOn(storageObj, 'remove');
            storageObj.remove();
            expect(storageObj.remove).toHaveBeenCalled();
        });
    });
});
