/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

                /**
                 * Recursive open.
                 */
                recursiveOpen = function () {
                    var el = $('[data-id="' + path.pop() + '"]');

                    if (path.length > 1) {
                        tree.jstree('open_node', el, recursiveOpen);
                    } else {
                        tree.jstree('open_node', el, function () {
                            tree.jstree('select_node', el);
                        });
                    }
                };

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
                    state: 'closed'
                };
            });
        }
    });

    return $.mage.folderTree;
});
