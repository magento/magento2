/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, FileUploader) {
    'use strict';

    describe('Magento_Ui/js/form/element/file-uploader', function () {
        var component;

        beforeEach(function () {
            component = new FileUploader({
                dataScope: 'abstract'
            });
        });

        describe('initUploader method', function () {
            it('creates instance of file uploader', function () {
                var elem = document.createElement('input');

                spyOn($.fn, 'fileupload');

                component.initUploader(elem);

                expect($.fn.fileupload).toHaveBeenCalled();
            });
        });

        describe('isFileAllowed method', function () {
            var invalidFile,
                validFile;

            invalidFile = {
                size: 2000,
                name: 'name.txt'
            };

            validFile = {
                size: 500,
                name: 'name.jpg'
            };

            it('validates file extension', function () {
                var valid,
                    invalid;

                component.allowedExtensions = ['jpg'];
                component.maxFileSize = false;

                valid   = component.isFileAllowed(validFile);
                invalid = component.isFileAllowed(invalidFile);

                expect(valid.passed).toBe(true);
                expect(invalid.passed).toBe(false);
            });

            it('validates file size', function () {
                var valid,
                    invalid;

                component.allowedExtensions = [];
                component.maxFileSize = 1000;

                valid = component.isFileAllowed(validFile);
                invalid = component.isFileAllowed(invalidFile);

                expect(valid.passed).toBe(true);
                expect(invalid.passed).toBe(false);
            });
        });

        describe('formatSize method', function () {
            it('converts bytes value to a more readable string representation', function () {
                var bytes       = 28912,
                    expected    = '28 KB',
                    result      = component.formatSize(bytes);

                expect(result).toEqual(expected);
            });
        });

        describe('reset method', function () {
            it('restores initial files set', function () {
                var file1 = {},
                    file2 = {};

                component.initialValue = [file1];

                component.addFile(file2);
                component.reset();

                expect(component.value()).toEqual(jasmine.arrayContaining([file1]));
                expect(component.value()).not.toEqual(jasmine.arrayContaining([file2]));
            });
        });

        describe('hasChanged method', function () {
            it('checks if files set is different from its initial value', function () {
                component.initialValue = [{}];

                component.addFile({});

                expect(component.hasChanged()).toBe(true);

                component.reset();

                expect(component.hasChanged()).toBe(false);
            });
        });

        describe('clear method', function () {
            it('removes all files from collection', function () {
                var file = {};

                component.addFile(file);

                expect(component.value().length).toBeGreaterThan(0);

                component.clear();

                expect(component.value().length).toEqual(0);
            });

            it('returns instance of component', function () {
                var instance = component.clear();

                expect(instance).toEqual(component);
            });
        });

        describe('addFile method', function () {
            it('adds single file to collection', function () {
                var file1 = {},
                    file2 = {};

                this.isMultipleFiles = false;

                component.addFile(file1);
                component.addFile(file2);

                expect(component.value()).toEqual(jasmine.arrayContaining([file2]));
                expect(component.value().length).toEqual(1);
            });

            it('adds multiple files to collection', function () {
                var file1 = {},
                    file2 = {};

                this.isMultipleFiles = true;

                component.addFile(file1);
                component.addFile(file2);

                expect(component.value()).toEqual(jasmine.arrayContaining([file1, file2]));

                this.isMultipleFiles = false;
            });

            it('returns instance of component', function () {
                var instance = component.addFile({});

                expect(instance).toEqual(component);
            });
        });

        describe('removeFile method', function () {
            it('removes single file from collection', function () {
                var file = {};

                component.addFile(file);
                component.removeFile(file);

                expect(component.value()).not.toEqual(jasmine.arrayContaining([file]));
            });

            it('returns instance of component', function () {
                var instance = component.removeFile({});

                expect(instance).toEqual(component);
            });
        });

        describe('getFile method', function () {
            it('returns instance of a file found by search criteria', function () {
                var matchedFile,
                    file = {};

                component.addFile(file);

                matchedFile = component.getFile(function (item) {
                    return item === file;
                });

                expect(matchedFile).toEqual(file);
            });
        });

        describe('hasData method', function () {
            it('checks that collection has some items', function () {
                var file = {};

                component.addFile(file);

                expect(component.hasData()).toBe(true);

                component.clear();

                expect(component.hasData()).toBe(false);
            });
        });

        describe('onLoadingStart method', function () {
            it('sets isLoading flag to be true', function () {
                component.isLoading = false;
                component.onLoadingStart();

                expect(component.isLoading).toBe(true);
            });
        });

        describe('onLoadingStop method', function () {
            it('drops isLoading flag', function () {
                component.isLoading = true;
                component.onLoadingStop();

                expect(component.isLoading).toBe(false);
            });
        });

        describe('onFileUploaded handler', function () {
            it('calls addFile method if upload was successful', function () {
                spyOn(component, 'aggregateError');
                spyOn(component, 'addFile');

                component.onFileUploaded({}, {
                    files: [{
                        name: 'hello.jpg'
                    }],
                    result: {
                        error: false
                    }
                });

                expect(component.aggregateError).not.toHaveBeenCalled();
                expect(component.addFile).toHaveBeenCalled();
            });

            it('should call uploaderConfig.stop when number of errors is equal to number of files', function () {
                var fakeEvent = {
                        target: document.createElement('input')
                    },
                    file = {
                        name: 'hello.jpg'
                    },
                    data = {
                        files: [file],
                        originalFiles: [file]
                    };

                spyOn(component, 'isFileAllowed').and.callFake(function (fileArg) {
                    expect(fileArg).toBe(file);

                    return {
                        passed: false,
                        message: 'Not awesome enough'
                    };
                });
                component.initUploader();
                spyOn(component.uploaderConfig, 'done');
                spyOn(component.uploaderConfig, 'stop');
                component.onBeforeFileUpload(fakeEvent, data);
                expect(component.uploaderConfig.stop).toHaveBeenCalled();
            });
            it('should not call uploaderConfig.stop when number of errors is unequal to number of files', function () {
                var fakeEvent = {
                        target: document.createElement('input')
                    },
                    file = {
                        name: 'hello.jpg'
                    },
                    otherFileInQueue = {
                        name: 'world.png'
                    },
                    data = {
                        files: [file],
                        originalFiles: [file, otherFileInQueue]
                    };

                component.initUploader();
                spyOn(component.uploaderConfig, 'done');
                spyOn(component.uploaderConfig, 'stop');
                spyOn(component, 'isFileAllowed').and.callFake(function (fileArg) {
                    expect(fileArg).toBe(file);

                    return {
                        passed: false,
                        message: 'Not awesome enough'
                    };
                });

                component.onBeforeFileUpload(fakeEvent, data);
                expect(component.uploaderConfig.stop).not.toHaveBeenCalled();
            });
        });

        describe('onElementRender handler', function () {
            it('invokes initUploader method', function () {
                var input = document.createElement('input');

                spyOn(component, 'initUploader');

                component.onElementRender(input);

                expect(component.initUploader).toHaveBeenCalledWith(input);
            });
        });

        describe('aggregateError method', function () {
            it('should append onto aggregatedErrors array when called', function () {
                spyOn(component.aggregatedErrors, 'push');

                component.aggregateError('blah.jpg', 'File is too awesome');

                expect(component.aggregatedErrors.push).toHaveBeenCalledWith({
                    filename: 'blah.jpg',
                    message: 'File is too awesome'
                });
            });
        });
    });
});
