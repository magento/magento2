/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/controls/bookmarks/bookmarks'
], function (Bookmarks) {
    'use strict';
    describe('ui/js/grid/controls/bookmarks/bookmarks', function () {
        var bookmarksElement, returnContext;

        beforeEach(function () {
            bookmarksElement = new Bookmarks({
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });

        });
        it('has initialize method', function () {
            spyOn(bookmarksElement, "initialize");
            bookmarksElement.initialize();
            expect(bookmarksElement.initialize).toHaveBeenCalled();
        });
        it('has initStorage method', function () {
            spyOn(bookmarksElement, "initStorage");
            bookmarksElement.initStorage();
            expect(bookmarksElement.initStorage).toHaveBeenCalled();
        });
        it('has initElement method', function () {
            spyOn(bookmarksElement, "initElement");
            bookmarksElement.initElement();
            expect(bookmarksElement.initElement).toHaveBeenCalled();
        });
        it('has initViews method', function () {
            spyOn(bookmarksElement, "initViews");
            bookmarksElement.initViews();
            expect(bookmarksElement.initViews).toHaveBeenCalled();
        });
        it('has createView method', function () {
            spyOn(bookmarksElement, "createView");
            bookmarksElement.createView();
            expect(bookmarksElement.createView).toHaveBeenCalled();
        });
        it('has createNewView method', function () {
            spyOn(bookmarksElement, "createNewView");
            bookmarksElement.createNewView();
            expect(bookmarksElement.createNewView).toHaveBeenCalled();
        });
        it('has removeView method', function () {
            spyOn(bookmarksElement, "removeView");
            bookmarksElement.removeView();
            expect(bookmarksElement.removeView).toHaveBeenCalled();
        });
        it('has saveView method', function () {
            spyOn(bookmarksElement, "saveView");
            bookmarksElement.saveView();
            expect(bookmarksElement.saveView).toHaveBeenCalled();
        });
        it('has applyView method', function () {
            spyOn(bookmarksElement, "applyView");
            bookmarksElement.applyView();
            expect(bookmarksElement.applyView).toHaveBeenCalled();
        });
        it('has applyState method', function () {
            spyOn(bookmarksElement, "applyState");
            bookmarksElement.applyState();
            expect(bookmarksElement.applyState).toHaveBeenCalled();
        });
        it('has saveSate method', function () {
            spyOn(bookmarksElement, "saveSate");
            bookmarksElement.saveSate();
            expect(bookmarksElement.saveSate).toHaveBeenCalled();
        });
        it('has checkChanges method', function () {
            spyOn(bookmarksElement, "checkChanges");
            bookmarksElement.checkChanges();
            expect(bookmarksElement.checkChanges).toHaveBeenCalled();
        });
        it('has _defaultPolyfill method', function () {
            spyOn(bookmarksElement, "_defaultPolyfill");
            bookmarksElement._defaultPolyfill();
            expect(bookmarksElement._defaultPolyfill).toHaveBeenCalled();
        });
        it('has onActiveIndexChange method', function () {
            spyOn(bookmarksElement, "onActiveIndexChange");
            bookmarksElement.onActiveIndexChange();
            expect(bookmarksElement.onActiveIndexChange).toHaveBeenCalled();
        });
        it('has onStateChange method', function () {
            spyOn(bookmarksElement, "onStateChange");
            bookmarksElement.onStateChange();
            expect(bookmarksElement.onStateChange).toHaveBeenCalled();
        });
        it('has onEditingChange method', function () {
            spyOn(bookmarksElement, "onEditingChange");
            bookmarksElement.onEditingChange();
            expect(bookmarksElement.onEditingChange).toHaveBeenCalled();
        });
    });
});
