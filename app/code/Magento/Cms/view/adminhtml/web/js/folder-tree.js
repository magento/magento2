/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define(["jquery","jquery/ui","jquery/jstree/jquery.jstree"], function($){
    'use strict';

    $.widget("mage.folderTree", {
        options: {
            root: 'root',
            rootName: 'Root',
            url: '',
            currentPath: ['root'],
            tree: {
                "plugins": ["themes", "json_data", "ui", "hotkeys"],
                "themes": {
                    "theme": "default",
                    "dots": false,
                    "icons": true
                }
            }
        },
        _create: function() {
            var options = this.options;
            var treeOptions = $.extend(
                true,
                {},
                options.tree,
                {
                    json_data: {
                        data: {
                            data: options.rootName,
                            state: "closed",
                            metadata: {node: {id: options.root, text: options.rootName}},
                            attr: { "data-id": options.root, id: options.root}
                        },
                        ajax: {
                            url: options.url,
                            data: function(node) {
                                return {
                                    node: node.data('id'),
                                    form_key: window.FORM_KEY
                                };
                            },
                            success: this._convertData
                        }
                    }
                }
            );
            this.element.jstree(treeOptions).on('loaded.jstree', $.proxy(this.treeLoaded, this));
        },

        treeLoaded: function(event) {
            var path = this.options.currentPath;
            var tree = this.element;
            var recursiveOpen = function() {
                var el = $("[data-id=\"" + path.pop() + "\"]");
                if (path.length > 1) {
                    tree.jstree('open_node', el, recursiveOpen);
                } else {
                    tree.jstree('open_node', el, function() {
                        tree.jstree('select_node', el);
                    });
                }
            };
            recursiveOpen();
        },

        _convertData: function(data) {
            return  $.map(data, function(node) {
                var codeCopy = $.extend({}, node);
                return {
                    data: node.text,
                    attr: {"data-id": node.id, id: node.id},
                    metadata: {node: codeCopy},
                    state: "closed"
                };
            });
        }
    });
});