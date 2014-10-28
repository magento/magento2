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

});