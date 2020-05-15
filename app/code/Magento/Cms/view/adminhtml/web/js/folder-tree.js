/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Base64 */
define([
    'jquery',
    'jquery/ui',
    'jquery/jstree/jquery.jstree'
], function ($) {
    'use strict';

    $.widget('mage.folderTree', {
        options: {
            root: 'root',
            rootName: 'Root',
            url: '',
            currentPath: ['root'],
            tree: {
                'plugins': ['themes', 'json_data', 'ui', 'hotkeys'],
                'themes': {
                    'theme': 'default',
                    'dots': false,
                    'icons': true
                }
            }
        },

        /** @inheritdoc */
        _create: function () {
            var options = this.options,
                treeOptions = $.extend(
                    true,
                    {},
                    options.tree,
                    {
                        'json_data': {
                            data: {
                                data: options.rootName,
                                state: 'closed',
                                metadata: {
                                    node: {
                                        id: options.root,
                                        text: options.rootName
                                    }
                                },
                                attr: {
                                    'data-id': options.root,
                                    id: options.root
                                }
                            },
                            ajax: {
                                url: options.url,

                                /**
                                 * @param {Object} node
                                 * @return {Object}
                                 */
                                data: function (node) {
                                    return {
                                        node: node.data('id'),
                                        'form_key': window.FORM_KEY
                                    };
                                },
                                success: this._convertData
                            }
                        }
                    }
                );

            this.element.jstree(treeOptions).on('loaded.jstree', $.proxy(this.treeLoaded, this));
        },

        /**
         * Tree loaded.
         */
        treeLoaded: function () {
            var path = this.options.currentPath,
                tree = this.element,
                lastExistentFolderEl,
                pathId,
                encodedPath,

                /**
                 * Recursively open folders specified in path array.
                 */
                recursiveOpen = function () {
                    var folderEl = $('[data-id="' + path.pop() + '"]');

                    // if folder doesn't exist, select the last opened folder
                    if (!folderEl.length) {
                        tree.jstree('select_node', lastExistentFolderEl);

                        return;
                    }

                    lastExistentFolderEl = folderEl;

                    if (path.length) {
                        tree.jstree('open_node', folderEl, recursiveOpen);
                    } else {
                        tree.jstree('open_node', folderEl, function () {
                            tree.jstree('select_node', folderEl);
                        });
                    }
                },

                /**
                 * Get currentPath decode it returns new path array
                 */
                _parseCurrentPath = function () {
                    var paths = [];

                    $.each(encodedPath, function (i, val) {
                        var isLastElement = i === encodedPath.length - 1;

                        if (isLastElement) {
                            paths[i] = window.MediabrowserUtility.pathId.replace(',,', '--');
                        } else {
                            paths[i] = Base64.encode(val).replace('==', '--');
                        }
                    });
                    paths.unshift('root');
                    paths.reverse();

                    return paths;
                };

            $(window).on('reload.MediaGallery', function () {
                encodedPath = Base64.decode(window.MediabrowserUtility.pathId.replace(/--|,,/, '==')).split('/');
                pathId =  window.MediabrowserUtility.pathId.replace(',,', '--');

                tree.jstree('deselect_all');

                if (encodedPath.length > 1) {
                    path =  _parseCurrentPath(encodedPath);
                    recursiveOpen();
                } else {
                    tree.jstree('open_node', $('[data-id="' + pathId + '"]'), function () {
                        tree.jstree('select_node',  $('[data-id="' + pathId + '"]'));
                    });
                }
            });

            recursiveOpen();
        },

        /**
         * @param {*} data
         * @return {*}
         * @private
         */
        _convertData: function (data) {
            return $.map(data, function (node) {
                var codeCopy = $.extend({}, node);

                return {
                    data: node.text,
                    attr: {
                        'data-id': node.id,
                        id: node.id
                    },
                    metadata: {
                        node: codeCopy
                    },
                    state: node.state || 'closed'
                };
            });
        }
    });

    return $.mage.folderTree;
});
