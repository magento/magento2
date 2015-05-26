/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/bookmarks'
], function (Bookmarks) {
    'use strict';
    describe('ui/js/grid/controls/bookmarks/bookmarks', function () {
        var BookmarksElement, returnContextOfItself;
        beforeEach(function () {
            BookmarksElement = Bookmarks();
        });
        it('has initObservable method', function () {
            returnContextOfItself = BookmarksElement.initObservable();
            expect(BookmarksElement.initObservable).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has initStorage method', function () {
            BookmarksElement.initStorage();
            expect(BookmarksElement.initStorage).toHaveBeenCalled();
        });
        it('has initElement method', function () {
            BookmarksElement.initElement();
            expect(BookmarksElement.initElement).toHaveBeenCalled();
        });
        it('has initViews method', function () {
            returnContextOfItself = BookmarksElement.initViews();
            expect(BookmarksElement.initViews).toHaveBeenCalled();
            expect(BookmarksElement.activeIndex).toBe('');
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has createView method', function () {
            returnContextOfItself = BookmarksElement.createView();
            expect(BookmarksElement.createView).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has createNewView method', function () {
            returnContextOfItself = BookmarksElement.createNewView();
            expect(BookmarksElement.createNewView).toHaveBeenCalled();
            expect(BookmarksElement.createView).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has removeView method', function () {
            returnContextOfItself = BookmarksElement.removeView();
            expect(BookmarksElement.removeView).toHaveBeenCalled();
            expect(BookmarksElement.removeStored).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has saveView method', function () {
            returnContextOfItself = BookmarksElement.saveView();
            expect(BookmarksElement.saveView).toHaveBeenCalled();
            expect(BookmarksElement.hasChanges).toHaveBeenCalled();
            expect(BookmarksElement.store).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has saveCurrent method', function () {
            returnContextOfItself = BookmarksElement.saveCurrent();
            expect(BookmarksElement.saveCurrent).toHaveBeenCalled();
            expect(BookmarksElement.store).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has checkChanges method', function () {
            returnContextOfItself = BookmarksElement.checkChanges();
            expect(BookmarksElement.checkChanges).toHaveBeenCalled();
            expect(BookmarksElement.activeView).toHaveBeenCalled();
            expect(BookmarksElement.hasChanges).toHaveBeenCalled();
            expect(returnContextOfItself).toBe(BookmarksElement);
        });
        it('has getSaved method', function () {
            returnContextOfItself = BookmarksElement.saveCurrent();
            expect(BookmarksElement.getSaved).toHaveBeenCalled();
            expect(BookmarksElement.activeView).toHaveBeenCalled();
            expect(BookmarksElement.getSaved).toHaveBeenCalled();
        });
        it('has getDefault method', function () {
            BookmarksElement.getDefault();
            expect(BookmarksElement.getDefault).toHaveBeenCalled();
        });
        it('has defaultPolyfill method', function () {
            BookmarksElement.saveCurrent();
            expect(BookmarksElement.defaultPolyfill).toHaveBeenCalled();
            expect(BookmarksElement.activeView).toHaveBeenCalled();
            expect(BookmarksElement.checkChanges).toHaveBeenCalled();
        });
        it('has onActiveChange method', function () {
            BookmarksElement.saveCurrent();
            expect(BookmarksElement.onActiveChange).toHaveBeenCalled();
            expect(BookmarksElement.store).toHaveBeenCalled();
            expect(BookmarksElement.activeView).toHaveBeenCalled();
            expect(BookmarksElement.hasChanges).toHaveBeenCalled();
            expect(BookmarksElement.initialSet).toBeFalsy();
        });
        it('has onDataChange method', function () {
            BookmarksElement.onDataChange();
            expect(BookmarksElement.onDataChange).toHaveBeenCalled();
            expect(BookmarksElement.saveCurrent).toHaveBeenCalled();
            expect(BookmarksElement.activeView).toHaveBeenCalled();
        });
        it('has onEditingChange method', function () {
            BookmarksElement.onEditingChange();
            expect(BookmarksElement.onEditingChange).toHaveBeenCalled();
        });
    });
});