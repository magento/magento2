/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_MediaGalleryUi/js/directory/directories',
    'uiComponent',
    'jquery'
], function (Directories, UiComponent, $) {
    'use strict';

    describe('Magento_MediaGalleryUi/js/directory/directories', function () {
        let componentInstance,
            deleteButtonMock,
            originalJqueryInit;

        beforeEach(function () {
            spyOn(Directories.prototype, 'initialize').and.callThrough();
            spyOn(UiComponent.prototype, 'observe');
            spyOn(Directories.prototype, 'initEvents');
            spyOn(Directories.prototype, '_addValidation').and.callThrough();
            spyOn($.validator, 'addMethod');

            deleteButtonMock = jasmine.createSpyObj('deleteButton', ['prop', 'removeClass', 'attr', 'addClass']);
            deleteButtonMock.prop.and.returnValue(deleteButtonMock);
            deleteButtonMock.attr.and.returnValue(deleteButtonMock);

            originalJqueryInit = $.fn.init;
            // eslint-disable-next-line max-nested-callbacks
            spyOn($.fn, 'init').and.callFake(function (selector) {
                if (selector === '#delete_folder') {
                    return deleteButtonMock;
                }

                return new originalJqueryInit(selector);
            });

            componentInstance = new Directories({
                name: 'test',
                template: 'test',
                allowedActions: []
            });

            componentInstance.selectedFolder = jasmine.createSpy('selectedFolder');
        });

        afterEach(function () {
            $.fn.init = originalJqueryInit;
        });

        it('should call necessary methods on initialization', function () {
            expect(componentInstance.initialize).toHaveBeenCalled();
            expect(componentInstance.observe).toHaveBeenCalledWith(['selectedFolder']);
            expect(componentInstance._addValidation).toHaveBeenCalled();
            expect(componentInstance.initEvents).toHaveBeenCalled();
        });

        it('should return the component instance on initialization', function () {
            expect(componentInstance.initialize()).toBe(componentInstance);
        });

        it('should set selectedFolder and enable delete button if allowed', function () {
            componentInstance.allowedActions = ['delete_folder'];
            componentInstance.setActive('my/folder');

            expect(componentInstance.selectedFolder).toHaveBeenCalledWith('my/folder');
            expect(deleteButtonMock.prop).toHaveBeenCalledWith('disabled', false);
            expect(deleteButtonMock.removeClass).toHaveBeenCalledWith('disabled');
        });

        it('should only set selectedFolder if delete is not allowed', function () {
            componentInstance.allowedActions = ['some_other_action'];
            componentInstance.setActive('my/folder');

            expect(componentInstance.selectedFolder).toHaveBeenCalledWith('my/folder');
            expect(deleteButtonMock.prop).not.toHaveBeenCalled();
            expect(deleteButtonMock.removeClass).not.toHaveBeenCalled();
        });

        it('should set selectedFolder to null and disable delete button', function () {
            componentInstance.setInActive();

            expect(componentInstance.selectedFolder).toHaveBeenCalledWith(null);
            expect(deleteButtonMock.attr).toHaveBeenCalledWith('disabled', true);
            expect(deleteButtonMock.addClass).toHaveBeenCalledWith('disabled');
        });

        it('should return only the folder name when no folder is selected', function () {
            componentInstance.selectedFolder.and.returnValue(null);
            expect(componentInstance.getNewFolderPath('new_folder')).toBe('new_folder');
        });

        it('should return the full path when a folder is selected', function () {
            componentInstance.selectedFolder.and.returnValue('path/to/folder');
            expect(componentInstance.getNewFolderPath('new_folder')).toBe('path/to/folder/new_folder');
        });
    });
});
