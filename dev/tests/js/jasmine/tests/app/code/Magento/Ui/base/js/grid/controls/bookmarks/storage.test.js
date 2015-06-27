/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/storage',
    'Magento_Ui/js/lib/storage'
], function (bookmarkStorage,storage) {
    'use strict';

    describe('ui/js/grid/controls/bookmarks/storage', function () {

        it('has getter method', function () {
            spyOn(storage, 'get');
            bookmarkStorage.get();
            expect(storage.get).toHaveBeenCalled();
            bookmarkStorage.get(1, 2, 3);
            expect(storage.get).toHaveBeenCalledWith(1, 2, 3);
            bookmarkStorage.get('string');
            expect(storage.get).toHaveBeenCalledWith('string');
        });

        it('has setter method', function () {
            spyOn(storage, 'set');
            bookmarkStorage.set();
            expect(storage.set).toHaveBeenCalled();
            bookmarkStorage.set(1,2);
            expect(storage.set).toHaveBeenCalledWith(1,2);
            bookmarkStorage.set('path', 'value');
            expect(storage.set).toHaveBeenCalledWith('path', 'value');
        });
        it('has remove method', function () {
            spyOn(storage, 'remove');
            bookmarkStorage.remove();
            expect(storage.remove).toHaveBeenCalled();
            bookmarkStorage.remove(1,2);
            expect(storage.remove).toHaveBeenCalledWith(1,2);
            bookmarkStorage.remove('path');
            expect(storage.remove).toHaveBeenCalledWith('path');
        });

    });
});
