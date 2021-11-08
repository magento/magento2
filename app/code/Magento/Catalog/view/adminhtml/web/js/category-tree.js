/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mageUtils',
    'jquery/ui',
    'jquery/jstree/jquery.jstree'
], function ($, utils) {
    'use strict';

    $.widget('mage.categoryTree', {
        options: {
            url: '',
            data: [],
            tree: {
                core: {
                    themes: {
                        dots: false
                    }
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
                        core: {
                            data: this._convertData(this.options.data).children
                        }
                    }
                );

            this.element.jstree(treeOptions);
            this.element.on('select_node.jstree', $.proxy(this._selectNode, this));
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _selectNode: function (event, data) {
            var node = data.node;

            if (!node.state.disabled) {
                window.location = window.location + '/' + node.id;
            } else {
                event.preventDefault();
            }
        },

        /**
         * @param {Array} nodes
         * @returns {Array}
         * @private
         */
        _convertDataNodes: function (nodes) {
            var nodesData = [];

            nodes.children.forEach(function (node) {
                nodesData.push(this._convertData(node));
            }, this);

            return nodesData;
        },

        /**
         * @param {Object} node
         * @return {*}
         * @private
         */
        _convertData: function (node) {
            var self = this,
                result;

            if (!node) {
                return result;
            }
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            result = {
                id: node.id,
                text: utils.unescape(node.name) + ' (' + node.product_count + ')',
                li_attr: {
                    class: node.cls + (!!node.disabled ? ' disabled' : '') //eslint-disable-line no-extra-boolean-cast
                },
                state: {
                    disabled: node.disabled,
                    opened:  !!node.children_count && node.expanded
                }
            };
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            if (node.children) {
                result.children = [];
                $.each(node.children, function () {
                    result.children.push(self._convertData(this));
                });
            }

            return result;
        }
    });

    return $.mage.categoryTree;
});
