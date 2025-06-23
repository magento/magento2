/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
define([
    'jquery',
    'Magento_Catalog/js/category-checkbox-tree'
], function ($, CategoryCheckboxTree) {
    'use strict';

    let $tree,
        $ajaxOriginal,
        $ajaxMock,
        jsFormObject,
        inputElement,
        treeContainerElement,
        treeJson = {
            root: [
                {
                    'text': 'Default Category (10000)',
                    'id': '2',
                    'store': 0,
                    'path': '1/2',
                    'a_attr': {
                        'class': 'active-category'
                    },
                    'allowDrop': true,
                    'allowDrag': false,
                    'children': [
                        {
                            'text': 'Category 1 (1053)',
                            'id': '3',
                            'store': 0,
                            'path': '1/2/3',
                            'a_attr': {
                                'class': 'active-category'
                            },
                            'allowDrop': true,
                            'allowDrag': true,
                            'children': [
                                {
                                    'text': 'Category 1.1 (510)',
                                    'id': '4',
                                    'store': 0,
                                    'path': '1/2/3/4',
                                    'a_attr': {
                                        'class': 'active-category'
                                    },
                                    'allowDrop': true,
                                    'allowDrag': true,
                                    'children': [
                                        {
                                            'text': 'Category 1.1.1 (34)',
                                            'id': '5',
                                            'store': 0,
                                            'path': '1/2/3/4/5',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true,
                                            'children': []
                                        },
                                        {
                                            'text': 'Category 1.1.2 (34)',
                                            'id': '12',
                                            'store': 0,
                                            'path': '1/2/3/4/12',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true,
                                            'children': []
                                        }
                                    ]
                                },
                                {
                                    'text': 'Category 1.2 (510)',
                                    'id': '19',
                                    'store': 0,
                                    'path': '1/2/3/19',
                                    'a_attr': {
                                        'class': 'active-category'
                                    },
                                    'allowDrop': true,
                                    'allowDrag': true,
                                    'children': [
                                        {
                                            'text': 'Category 1.2.1 (34)',
                                            'id': '20',
                                            'store': 0,
                                            'path': '1/2/3/19/20',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true,
                                            'children': []
                                        },
                                        {
                                            'text': 'Category 1.2.2 (34)',
                                            'id': '27',
                                            'store': 0,
                                            'path': '1/2/3/19/27',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true,
                                            'children': []
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            'text': 'Category 2 (1054)',
                            'id': '34',
                            'store': 0,
                            'path': '1/2/34',
                            'a_attr': {
                                'class': 'active-category'
                            },
                            'allowDrop': true,
                            'allowDrag': true,
                            'children': [
                                {
                                    'text': 'Category 2.1 (510)',
                                    'id': '35',
                                    'store': 0,
                                    'path': '1/2/34/35',
                                    'a_attr': {
                                        'class': 'active-category'
                                    },
                                    'allowDrop': true,
                                    'allowDrag': true,
                                    'children': [
                                        {
                                            'text': 'Category 2.1.1 (34)',
                                            'id': '36',
                                            'store': 0,
                                            'path': '1/2/34/35/36',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true
                                        },
                                        {
                                            'text': 'Category 2.1.2 (34)',
                                            'id': '43',
                                            'store': 0,
                                            'path': '1/2/34/35/43',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true
                                        }
                                    ]
                                },
                                {
                                    'text': 'Category 2.2 (510)',
                                    'id': '50',
                                    'store': 0,
                                    'path': '1/2/34/50',
                                    'a_attr': {
                                        'class': 'active-category'
                                    },
                                    'allowDrop': true,
                                    'allowDrag': true,
                                    'children': [
                                        {
                                            'text': 'Category 2.2.1 (34)',
                                            'id': '51',
                                            'store': 0,
                                            'path': '1/2/34/50/51',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true
                                        },
                                        {
                                            'text': 'Category 2.2.2 (34)',
                                            'id': '58',
                                            'store': 0,
                                            'path': '1/2/34/50/58',
                                            'a_attr': {
                                                'class': 'active-category'
                                            },
                                            'allowDrop': true,
                                            'allowDrag': true
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    'expanded': true
                }
            ],
            '5': [
                {
                    'text': 'Category 1.1.1.1 (34)',
                    'id': '6',
                    'path': '1/2/3/4/5/6',
                    'cls': 'folder active-category',
                    'allowDrop': false,
                    'allowDrag': false,
                    'children': [
                        {
                            'text': 'Category 1.1.1.1.1 (34)',
                            'id': '7',
                            'path': '1/2/3/4/5/6/7',
                            'cls': 'folder active-category',
                            'allowDrop': false,
                            'allowDrag': false,
                            'expanded': false
                        },
                        {
                            'text': 'Category 1.1.1.1.2 (34)',
                            'id': '8',
                            'path': '1/2/3/4/5/6/8',
                            'cls': 'folder active-category',
                            'allowDrop': false,
                            'allowDrag': false,
                            'expanded': false
                        }
                    ],
                    'expanded': false
                },
                {
                    'text': 'Category 1.1.1.2 (34)',
                    'id': '9',
                    'path': '1/2/3/4/5/9',
                    'cls': 'folder active-category',
                    'allowDrop': false,
                    'allowDrag': false,
                    'children': [
                        {
                            'text': 'Category 1.1.1.2.1 (34)',
                            'id': '10',
                            'path': '1/2/3/4/5/9/10',
                            'cls': 'folder active-category',
                            'allowDrop': false,
                            'allowDrag': false,
                            'expanded': false
                        },
                        {
                            'text': 'Category 1.1.1.2.2 (34)',
                            'id': '11',
                            'path': '1/2/3/4/5/9/11',
                            'cls': 'folder active-category',
                            'allowDrop': false,
                            'allowDrag': false,
                            'expanded': false
                        }
                    ],
                    'expanded': false
                }
            ]
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

    describe('Magento_Catalog/js/category-checkbox-tree', function () {
        beforeEach(function () {
            inputElement = document.createElement('input');
            inputElement.defaultValue = '7,10';
            treeContainerElement = document.createElement('div');
            treeContainerElement.id = 'tree-container-' + Math.random().toString(36).substring(7);
            jsFormObject = {
                updateElement: inputElement
            };
            window.jsFormObject = jsFormObject;
            document.body.appendChild(inputElement);
            document.body.appendChild(treeContainerElement);
            $tree = $(treeContainerElement);
            $ajaxMock = jasmine.createSpy('ajaxMock');
            $ajaxOriginal = $.ajax;
            $.ajax = $ajaxMock;
            return waitUntil(function (resolve) {
                $tree.on('loaded.jstree', function () {
                    resolve();
                });

                CategoryCheckboxTree({
                    dataUrl: 'http://localhost/categoriesJson/',
                    divId: treeContainerElement.id,
                    jsFormObject: 'jsFormObject',
                    treeJson: treeJson.root
                });
            });
        });

        afterEach(function () {
            inputElement.remove();
            treeContainerElement.remove();
            $tree = null;
            $.ajax = $ajaxOriginal;
        });

        it('should create successfully the tree', function () {
            expect($tree.jstree(true).is_loaded('#')).toBeTrue();
        });

        it('should load successfully the tree elements', function () {
            // node 2
            expect($tree.jstree(true).is_loaded(2)).toBeTrue();
            expect($tree.jstree(true).is_parent(2)).toBeTrue();
            expect($tree.jstree(true).is_open(2)).toBeTrue();

            // node 3
            expect($tree.jstree(true).is_loaded(3)).toBeTrue();
            expect($tree.jstree(true).is_parent(3)).toBeTrue();
            expect($tree.jstree(true).is_open(3)).toBeFalse();

            // node 4
            expect($tree.jstree(true).is_loaded(4)).toBeTrue();
            expect($tree.jstree(true).is_parent(4)).toBeTrue();
            expect($tree.jstree(true).is_open(4)).toBeFalse();

            // node 5
            expect($tree.jstree(true).is_loaded(5)).toBeFalse();
            expect($tree.jstree(true).is_parent(5)).toBeTrue();
            expect($tree.jstree(true).is_open(4)).toBeFalse();
        });

        it('should be able to open node with loaded children', function () {
            const node = '3';

            return waitUntil(function (resolve) {
                $tree.on('open_node.jstree', function (e, data) {
                    expect(data.node.id).toEqual(node);
                    expect($tree.jstree(true).is_parent(node)).toBeTrue();
                    resolve();
                });

                $tree.jstree(true).open_node(node);
            });
        });

        it('should be able to open node with unloaded children', function () {
            const node = '5';

            $ajaxMock.and.callFake(function (options) {
                options.success(treeJson[node]);
            });
            expect($tree.jstree(true).is_loaded(node)).toBeFalse();
            return waitUntil(function (resolve) {
                $tree.on('load_node.jstree', function (e, data) {
                    expect(data.node.id).toEqual(node);
                    expect($tree.jstree(true).is_parent(node)).toBeTrue();
                    expect(data.node.children_d).toEqual(['6', '7', '8', '9', '10', '11']);
                    expect($ajaxMock).toHaveBeenCalled();
                    resolve();
                });

                $tree.jstree(true).open_node(node);
            });
        });

        it('should not be able to open node with no children', function () {
            const node = '36';

            $tree.jstree(true).open_node(node);

            expect($tree.jstree(true).is_parent(node)).toBeFalse();
            expect($ajaxMock).not.toHaveBeenCalled();
        });

        it('should update the target input when selected', function () {
            const node = '3';

            return waitUntil(function (resolve) {
                $tree.on('changed.jstree', function (e, data) {
                    expect(data.node.id).toEqual(node);
                    expect(inputElement.value).toEqual('3,7,10');
                    resolve();
                });

                $tree.jstree(true).select_node(node);
            });
        });
    });
});
