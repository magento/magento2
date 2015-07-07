/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/view'
], function (BookmarkView) {
        'use strict';
    describe('ui/js/grid/controls/bookmarks/view', function () {
        var view, returnContextOfItself;
        beforeEach(function(){
            view = new BookmarkView({
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
        });
        it('has initObservable method', function () {
            spyOn(view, "initObservable");
            view.initObservable();
            expect(view.initObservable).toHaveBeenCalled();
        });

    })
});
