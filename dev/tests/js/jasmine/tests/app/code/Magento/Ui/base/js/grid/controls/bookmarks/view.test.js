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
        it('has initialize method', function () {
            spyOn(view, "initialize");
            view.initialize();
            expect(view.initialize).toHaveBeenCalled();
        });
        it('has initObservable method', function () {
            spyOn(view, "initObservable");
            view.initObservable();
            expect(view.initObservable).toHaveBeenCalled();
        });
        it('has getData method', function () {
            spyOn(view, "getData");
            view.getData();
            expect(view.getData).toHaveBeenCalled();
        });
        it('has setData method', function () {
            spyOn(view, "setData");
            view.setData();
            expect(view.setData).toHaveBeenCalled();
        });
        it('has syncLabel method', function () {
            spyOn(view, "syncLabel");
            view.syncLabel();
            expect(view.syncLabel).toHaveBeenCalled();
        });
        it('has startEdit method', function () {
            spyOn(view, "startEdit");
            view.startEdit();
            expect(view.startEdit).toHaveBeenCalled();
        });
        it('has exportView method', function () {
            spyOn(view, "exportView");
            view.exportView();
            expect(view.exportView).toHaveBeenCalled();
        });
        it('has onActivate method', function () {
            spyOn(view, "onActivate");
            view.onActivate();
            expect(view.onActivate).toHaveBeenCalled();
        });
        it('has onActiveChange method', function () {
            spyOn(view, "onActiveChange");
            view.onActiveChange();
            expect(view.onActiveChange).toHaveBeenCalled();
        });
    })
});
