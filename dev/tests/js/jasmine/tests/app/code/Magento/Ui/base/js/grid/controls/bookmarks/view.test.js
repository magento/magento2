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
            view = BookmarkView();
        });
        it('has initObservable method', function () {
            returnContextOfItself = view.initObservable();
            expect(view.initObservable).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(view);
        });
        it('has getSaved method', function () {
            view.getSaved();
            expect(view.getSaved).toHaveBeenCalled();
        });
        it('has getData method', function () {
            view.getData();
            expect(view.getData).toHaveBeenCalled();
        });
        it('has setData method', function () {
            returnContextOfItself = view.setData();
            expect(view.setData).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(view);
        });
        it('has setLabel method', function () {
            returnContextOfItself = view.setLabel();
            expect(view.setLabel).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(view);
        });
        it('has startEdit method', function () {
            returnContextOfItself = view.startEdit();
            expect(view.startEdit).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(view);
        });
        it('has endEdit method', function () {
            returnContextOfItself = view.endEdit();
            expect(view.endEdit).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(view);
        });
        it('has save method', function () {
            view.save();
            expect(view.save).toHaveBeenCalled();
            expect(view.isNew).toBeFalsy();
        });
        it('has checkChanges method', function () {
            view.checkChanges();
            expect(view.checkChanges).toHaveBeenCalled();
            expect(view.changed).toHaveBeenCalled();
        });
        it('has onActivate method', function () {
            view.onActivate();
            expect(view.onActivate).toHaveBeenCalled();
        });
        it('has onActiveChange method', function () {
            view.onActiveChange();
            expect(view.onActiveChange).toHaveBeenCalled();
        });
    })
});
