/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
define([
    'jquery',
    'Magento_Catalog/js/product/set/editor'
], function ($, Editor) {
    'use strict';

    const configJson = {
        saveUrl: 'http://localhost/admin/catalog/product_set/edit/id/4/',
        tree: {
            assigned: {
                selector: '#editor-assigned',
                placeholder: false,
                data: [
                    {
                        'text': 'Product Details',
                        'id': '7',
                        'cls': 'folder',
                        'allowDrop': true,
                        'allowDrag': true,
                        'children': [
                            {
                                'text': 'name',
                                'id': '73',
                                'cls': 'system-leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': false,
                                'entity_id': '73'
                            },
                            {
                                'text': 'sku',
                                'id': '74',
                                'cls': 'system-leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': false,
                                'entity_id': '74'
                            },
                            {
                                'text': 'sku_type',
                                'id': '124',
                                'cls': 'leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': true,
                                'entity_id': '122'
                            }
                        ]
                    },
                    {
                        'text': 'Content',
                        'id': '13',
                        'cls': 'folder',
                        'allowDrop': true,
                        'allowDrag': true,
                        'children': [
                            {
                                'text': 'short_description',
                                'id': '76',
                                'cls': 'system-leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': false,
                                'entity_id': '76'
                            },
                            {
                                'text': 'description',
                                'id': '75',
                                'cls': 'system-leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': false,
                                'entity_id': '75'
                            }
                        ]
                    },
                    {
                        'text': 'Bundle Items',
                        'id': '19',
                        'cls': 'folder',
                        'allowDrop': true,
                        'allowDrag': true,
                        'children': [
                            {
                                'text': 'shipment_type',
                                'id': '127',
                                'cls': 'leaf',
                                'allowDrop': false,
                                'allowDrag': true,
                                'leaf': true,
                                'is_user_defined': '0',
                                'is_unassignable': true,
                                'entity_id': '125'
                            }
                        ]
                    }
                ]
            },
            unassigned: {
                selector: '#editor-unassigned',
                placeholder: true,
                data: [
                    {
                        'text': 'manufacturer',
                        'id': '83',
                        'cls': 'leaf',
                        'allowDrop': false,
                        'allowDrag': true,
                        'leaf': true,
                        'is_user_defined': '1',
                        'entity_id': null
                    },
                    {
                        'text': 'color',
                        'id': '93',
                        'cls': 'leaf',
                        'allowDrop': false,
                        'allowDrag': true,
                        'leaf': true,
                        'is_user_defined': '1',
                        'entity_id': null
                    }
                ]
            }
        }
    };

    function waitUntil(callback) {
        return new Promise(function (resolve, reject) {
            let isResolved = false;

            callback(
                function () {
                    isResolved = true;
                    resolve();
                },
                reject
            );

            setTimeout(function () {
                // if the promise is not resolved after 60 seconds, fail the test
                if (!isResolved) {
                    expect('Timeout - Async function did not complete within 30000ms').toBeFalse();
                    reject();
                }
            }, 30000);
        });
    }

    function waitUntilReady(callback) {
        const promises = [];

        promises.push(waitUntil(function (resolve) {
            $('#editor-assigned').on('loaded.jstree', function (e, data) {
                resolve(data.instance);
            });
        }));
        promises.push(waitUntil(function (resolve) {
            $('#editor-unassigned').on('loaded.jstree', function (e, data) {
                resolve(data.instance);
            });
        }));
        promises.push(waitUntil(callback));

        return Promise.all(promises);
    }

    describe('Magento_Catalog/js/category-checkbox-tree', function () {
        let $container, editor;

        beforeEach(function () {
            $container = $('<div id="editor-assigned"></div><div id="editor-unassigned"></div>');
            $container.appendTo('body');
        });

        afterEach(function () {
            $container.remove();
            $container = null;
            editor = null;
        });

        describe('constructor', function () {
            it('should create successfully the editor with config', function () {
                editor = new Editor(configJson);
                expect(editor.unassigned).toBeInstanceOf(Editor.Tree);
                expect(editor.assigned).toBeInstanceOf(Editor.Tree);
            });

            it('should create successfully the editor without config', function () {
                editor = new Editor();
                expect(editor.unassigned).toBeInstanceOf(Editor.Tree);
                expect(editor.assigned).toBeInstanceOf(Editor.Tree);
            });
        });

        describe('readOnly = false', function () {
            let $ajaxMock, $ajaxOriginal, reqData;
            const expectedNoChangesReqData = {
                'attributes': [
                    [ '73', '7', 1, '73' ],
                    [ '74', '7', 2, '74' ],
                    [ '124', '7', 3, '122' ],
                    [ '76', '13', 1, '76' ],
                    [ '75', '13', 2, '75' ],
                    [ '127', '19', 1, '125' ]
                ],
                'groups': [
                    [ '7', 'Product Details', 1 ],
                    [ '13', 'Content', 2 ],
                    [ '19', 'Bundle Items', 3 ]
                ],
                'not_attributes': [],
                'removeGroups': [ ]
            };

            beforeEach(function () {
                $ajaxMock = jasmine.createSpy('ajaxMock');
                $ajaxOriginal = $.ajax;
                $.ajax = $ajaxMock;

                $ajaxMock.and.callFake(function (options) {
                    reqData = JSON.parse(options.data.data);
                });

                return waitUntilReady(function (resolve) {
                    editor = new Editor(configJson);
                    editor.error = jasmine.createSpy('error');
                    resolve();
                });
            });

            afterEach(function () {
                $.ajax = $ajaxOriginal;
            });

            it('should be able to save without changes', function () {
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should be able to reorder groups', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                // move "Bundle Items" to the second position
                editor.assigned.move(node, null, 1);
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '124', '7', 3, '122' ],
                        [ '127', '19', 1, '125' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '19', 'Bundle Items', 2 ],
                        [ '13', 'Content', 3 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ ]

                });
            });

            it('should be able to reorder attributes', function () {
                const parent = editor.assigned.find(null, [{_id: '7'}]),
                    node = editor.assigned.find(parent, [{_id: '74'}]);

                // move "sku" to the top in "Product Details"
                editor.assigned.move(node, parent, 'first');
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '74', '7', 1, '74' ],
                        [ '73', '7', 2, '73' ],
                        [ '124', '7', 3, '122' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle Items', 3 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ ]

                });
            });

            it('should be able to move an attribute to a different group', function () {
                const node = editor.assigned.find(editor.assigned.find(null, [{_id: '7'}]), [{_id: '73'}]);

                // move "name" to "Content"
                editor.assigned.move(node, editor.assigned.find(null, [{_id: '13'}]), 'first');
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '74', '7', 1, '74' ],
                        [ '124', '7', 2, '122' ],
                        [ '73', '13', 1, '73' ],
                        [ '76', '13', 2, '76' ],
                        [ '75', '13', 3, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle Items', 3 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ ]
                });
            });

            it('should be able to unassign an attribute', function () {
                const node = editor.assigned.find(editor.assigned.find(null, [{_id: '7'}]), [{_id: '124'}]);

                // unassign "sku_type"
                editor.unassigned.move(node);
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle Items', 3 ]
                    ],
                    'not_attributes': ['122'],
                    'removeGroups': []
                });
            });

            it('should be able to assign an attribute to a group', function () {
                const parent = editor.assigned.find(null, [{_id: '7'}]),
                    node = editor.unassigned.find(null, [{_id: '93'}]);

                // add "color" to "Product Details"
                editor.assigned.move(node, parent);
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '124', '7', 3, '122' ],
                        [ '93', '7', 4, null ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle Items', 3 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ ]
                });
            });

            it('should be able to add a group', function () {
                const id = editor.addGroup('New Group');

                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '124', '7', 3, '122' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle Items', 3 ],
                        [ id, 'New Group', 4 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': []
                });
            });

            it('should be able to rename a group', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                // rename "Bundle Items" to "Bundle"
                editor.assigned.rename(node, 'Bundle');
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '124', '7', 3, '122' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ],
                        [ '127', '19', 1, '125' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ],
                        [ '19', 'Bundle', 3 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ ]
                });
            });

            it('should be able to delete a group', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                editor.assigned.delete(node);
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual({
                    'attributes': [
                        [ '73', '7', 1, '73' ],
                        [ '74', '7', 2, '74' ],
                        [ '124', '7', 3, '122' ],
                        [ '76', '13', 1, '76' ],
                        [ '75', '13', 2, '75' ]
                    ],
                    'groups': [
                        [ '7', 'Product Details', 1 ],
                        [ '13', 'Content', 2 ]
                    ],
                    'not_attributes': [],
                    'removeGroups': [ '19']
                });
            });

            it('should not be able to unassign an unassignable attribute', function () {
                const node = editor.assigned.find(editor.assigned.find(null, [{_id: '7'}]), [{_id: '74'}]);

                // unassign "sku"
                expect(editor.unassigned.move(node)).toBeFalse();
                expect(editor.error).toHaveBeenCalledOnceWith(
                    Editor.ERRORS.UNASSIGN,
                    {node, tree: editor.assigned}
                );
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                // check that the group was not deleted
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to assign an attribute outside of a group', function () {
                const node = editor.unassigned.find(null, [{_id: '93'}]);

                // add "color" to the root
                expect(editor.assigned.move(node)).toBeFalse();
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                // check that the group was not deleted
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to add a group with existing name', function () {
                expect(editor.addGroup('Content')).toBeFalse();

                expect(editor.error).toHaveBeenCalledOnceWith(
                    Editor.ERRORS.VALIDATION,
                    {
                        tree: editor.assigned,
                        result: {property: 'text', value: 'Content', code: 'isUnique', isValid: false}
                    }
                );

                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to rename a group with existing name', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                // rename "Bundle Items" to "Content"
                expect(editor.assigned.rename(node, 'Content')).toBeFalse();

                expect(editor.error).toHaveBeenCalledOnceWith(
                    Editor.ERRORS.VALIDATION,
                    {
                        tree: editor.assigned,
                        result: {property: 'text', value: 'Content', code: 'isUnique', isValid: false}
                    }
                );

                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to unassign a group with unassignable attributes', function () {
                const node = editor.assigned.find(null, [{_id: '7'}]);

                expect(editor.unassigned.move(node)).toBeFalse();
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                // check that the group was not deleted
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to delete a group if nothing is selected', function () {
                editor.submit();
                expect(editor.error).toHaveBeenCalledOnceWith(Editor.ERRORS.GROUP_NOT_SELECTED);
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                expect(reqData).toEqual(expectedNoChangesReqData);
            });

            it('should not be able to delete a group with unassignable attributes', function () {
                const node = editor.assigned.find(null, [{_id: '7'}]);

                expect(editor.assigned.delete(node)).toBeFalse();
                expect(editor.error).toHaveBeenCalledOnceWith(
                    Editor.ERRORS.DELETE_GROUP,
                    {node, tree: editor.assigned}
                );
                editor.save();
                expect($ajaxMock).toHaveBeenCalled();
                // check that the group was not deleted
                expect(reqData).toEqual(expectedNoChangesReqData);
            });
        });

        describe('readOnly = true', function () {
            let $ajaxMock, $ajaxOriginal;

            beforeEach(function () {
                $ajaxMock = jasmine.createSpy('ajaxMock');
                $ajaxOriginal = $.ajax;
                $.ajax = $ajaxMock;

                return waitUntilReady(function (resolve) {
                    editor = new Editor({...configJson, readOnly: true});
                    editor.error = jasmine.createSpy('error');
                    resolve();
                });
            });

            afterEach(function () {
                $.ajax = $ajaxOriginal;
            });

            it('should not be able to call save()', function () {
                editor.save();
                expect(editor.error).toHaveBeenCalledOnceWith(Editor.ERRORS.READ_ONLY);
                expect($ajaxMock).not.toHaveBeenCalled();
            });

            it('should not be able to call submit()', function () {
                editor.submit();
                expect(editor.error).toHaveBeenCalledOnceWith(Editor.ERRORS.READ_ONLY);
                expect($ajaxMock).not.toHaveBeenCalled();
            });

            it('should not be able to call addGroup()', function () {
                editor.addGroup();
                expect(editor.error).toHaveBeenCalledOnceWith(Editor.ERRORS.READ_ONLY);
                expect($ajaxMock).not.toHaveBeenCalled();
            });

            it('should not be able to reorder groups', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                // move "Bundle Items" to the second position
                expect(editor.assigned.move(node, null, 1)).toBeFalse();
            });

            it('should not be able to reorder attributes', function () {
                const parent = editor.assigned.find(null, [{_id: '7'}]),
                    node = editor.assigned.find(parent, [{_id: '74'}]);

                // move "sku" to the top in "Product Details"
                expect(editor.assigned.move(node, parent, 'first')).toBeFalse();
            });

            it('should not be able to move attribute to a different group', function () {
                const node = editor.assigned.find(editor.assigned.find(null, [{_id: '7'}]), [{_id: '73'}]);

                // move "name" to "Content"
                expect(editor.assigned.move(node, editor.assigned.find(null, [{_id: '13'}]), 'first')).toBeFalse();
            });

            it('should not be able to unassign an attribute', function () {
                const node = editor.assigned.find(editor.assigned.find(null, [{_id: '7'}]), [{_id: '124'}]);

                // unassign "sku_type"
                expect(editor.unassigned.move(node)).toBeFalse();
            });

            it('should not be able to assign an attribute to a group', function () {
                const parent = editor.assigned.find(null, [{_id: '7'}]),
                    node = editor.unassigned.find(null, [{_id: '93'}]);

                // add "color" to "Product Details"
                expect(editor.assigned.move(node, parent)).toBeFalse();
            });

            it('should not be able to add a group', function () {
                expect(Editor.prototype.addGroup.call(editor, 'New Group')).toBeFalse();
            });

            it('should not be able to rename a group', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                // rename "Bundle Items" to "Bundle"
                expect(editor.assigned.rename(node, 'Bundle')).toBeFalse();
            });

            it('should not be able to delete a group', function () {
                const node = editor.assigned.find(null, [{_id: '19'}]);

                expect(editor.assigned.delete(node)).toBeFalse();
            });
        });

        describe('placeholder', function () {
            describe('initial data has nodes', function () {
                beforeEach(function () {
                    return waitUntilReady(function (resolve) {
                        editor = new Editor(configJson);
                        editor.error = jasmine.createSpy('error');
                        resolve();
                    });
                });

                it('should create placeholder when empty and remove it otherwise', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    // add "color" to "Product Details"
                    editor.assigned.move(editor.unassigned.find(null, [{_id: '93'}]), parent);
                    // add "manufacturer" to "Product Details"
                    editor.assigned.move(editor.unassigned.find(null, [{_id: '83'}]), parent);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();

                    // unassign "color"
                    editor.unassigned.move(editor.assigned.find(parent, [{_id: '93'}]));

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).toBeNull();
                });

                it('should not be assignable', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    // add "color" to "Product Details"
                    editor.assigned.move(editor.unassigned.find(null, [{_id: '93'}]), parent);
                    // add "manufacturer" to "Product Details"
                    editor.assigned.move(editor.unassigned.find(null, [{_id: '83'}]), parent);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();

                    // try to drag the placeholder to "Product Details"
                    expect(editor.assigned.move(editor.unassigned.find(null, [{_id: 'empty'}]), parent)).toBeFalse();
                });
            });
            describe('initial data is empty', function () {
                beforeEach(function () {
                    return waitUntilReady(function (resolve) {
                        editor = new Editor({
                            ...configJson,
                            tree: {
                                assigned: configJson.tree.assigned,
                                unassigned: {
                                    ...configJson.tree.unassigned,
                                    data: []
                                }
                            }
                        });
                        editor.error = jasmine.createSpy('error');
                        resolve();
                    });
                });

                it('should create placeholder when empty and remove it otherwise', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();

                    // unassign "sku_type"
                    editor.unassigned.move(editor.assigned.find(parent, [{_id: '124'}]));

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).toBeNull();
                });

                it('should not be assignable', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();

                    // try to drag the placeholder to "Product Details"
                    expect(editor.assigned.move(editor.unassigned.find(null, [{_id: 'empty'}]), parent)).toBeFalse();
                });
            });
            describe('initial data has empty node', function () {
                beforeEach(function () {
                    return waitUntilReady(function (resolve) {
                        editor = new Editor({
                            ...configJson,
                            tree: {
                                assigned: configJson.tree.assigned,
                                unassigned: {
                                    ...configJson.tree.unassigned,
                                    data: [
                                        {
                                            'id' :'empty',
                                            'text': 'No attributes available',
                                            'cls': 'folder',
                                            'allowDrop': false,
                                            'allowDrag': false
                                        }
                                    ]
                                }
                            }
                        });
                        editor.error = jasmine.createSpy('error');
                        resolve();
                    });
                });

                it('should create placeholder when empty and remove it otherwise', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();
                    expect(editor.unassigned.find(null, [{_id: 'empty'}]).text).toEqual('No attributes available');

                    // unassign "sku_type"
                    editor.unassigned.move(editor.assigned.find(parent, [{_id: '124'}]));

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).toBeNull();
                });

                it('should not be assignable', function () {
                    const parent = editor.assigned.find(null, [{_id: '7'}]);

                    expect(editor.unassigned.find(null, [{_id: 'empty'}])).not.toBeNull();

                    // try to drag the placeholder to "Product Details"
                    expect(editor.assigned.move(editor.unassigned.find(null, [{_id: 'empty'}]), parent)).toBeFalse();
                });
            });
        });
    });
});
