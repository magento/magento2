/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/jstree/jquery.jstree"
], function($){
    "use strict";

    $.widget("mage.categoryTree", {
        options: {
            url: '',
            data: [],
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
                        ajax: {
                            url: options.url,
                            type: 'POST',
                            success: $.proxy(function(node) {
                                return this._convertData(node[0]);
                            }, this),
                            data: function(node) {
                                return {
                                    id: $(node).data('id'),
                                    form_key: window.FORM_KEY
                                };
                            }
                        },
                        data: this._convertData(options.data).children,
                        progressive_render: true
                    }
                }
            );
            this.element.jstree(treeOptions);
            this.element.on("select_node.jstree", $.proxy(this._selectNode, this));
        },
        _selectNode: function(event, data) {
            var node = data.rslt.obj.data();
            if (!node.disabled) {
                window.location = window.location + '/' + node.id;
            } else {
                event.preventDefault();
            }
        },
        _convertData: function(node) {
            if (!node) {
                return;
            }
            var result = {
                data: {
                    title: node.name + ' (' + node.product_count + ')'
                },
                attr: {
                    "class": node.cls + (!!node.disabled ? ' disabled' : '')
                },
                metadata: {
                    id: node.id,
                    disabled: node.disabled
                }
            };
            if (node.children_count && !node.expanded) {
                result.state = 'closed';
            } else {
                result.state = 'open';
            }

            if (node.children) {
                result.children = [];
                var self = this;
                $.each(node.children, function() {
                    result.children.push(self._convertData(this));
                });
            }
            return result;
        }
    });
    
    return $.mage.categoryTree;
});