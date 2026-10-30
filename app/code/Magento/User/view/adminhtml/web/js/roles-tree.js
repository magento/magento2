/**
 * Copyright 2013 Adobe
 * All rights reserved.
 */

/**
 * @api
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/jstree/jquery.jstree',
    'mage/translate'
], function ($) {
    'use strict';

    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
    $.widget('mage.rolesTree', {
        options: {
            treeInitData: {},
            editFormSelector: '',
            resourceFieldName: 'resource[]',
            checkboxVisible: true
        },

        /** @inheritdoc */
        _create: function () {
            this.element.jstree({
                plugins: ['checkbox'],
                checkbox: {
                    three_state: false,
                    visible: this.options.checkboxVisible,
                    cascade: 'undetermined'
                },
                core: {
                    data: this.options.treeInitData,
                    themes: {
                        dots: false
                    }
                }
            });
            this._bind();
            this._createButtons();
        },

        _createButtons: function () {
            const $tree = $.jstree.reference(this.element),
                collapseAllButton = document.createElement('button'),
                expandAllButton = document.createElement('button'),
                expandUsedButton = document.createElement('button'),
                parent = this.element[0],
                ul = this.element.find('ul')[0];

            collapseAllButton.innerText = $.mage.__('Collapse all');
            collapseAllButton.addEventListener('click', function () {
                $tree.close_all();
            });

            expandAllButton.innerText = $.mage.__('Expand all');
            expandAllButton.addEventListener('click', function () {
                $tree.open_all();
            });

            expandUsedButton.innerText = $.mage.__('Expand selected');
            expandUsedButton.addEventListener('click', function () {
                const hasOpened = [];

                $tree.get_checked(true).forEach(function (node) {
                    $tree.open_node(node);
                    hasOpened.push(node.id);
                    for (let i = 0; i < node.parents.length - 1; i++) {
                        const id = node.parents[i];

                        if (!hasOpened.includes(id)) {
                            $tree.open_node($tree.get_node(id));
                            hasOpened.push(id);
                        }
                    }
                });
            });

            this.buttons = [
                collapseAllButton,
                expandAllButton,
                expandUsedButton
            ];

            this.buttons.forEach(function (button) {
                button.type = 'button';
                parent.insertBefore(button, ul);
            });
        },

        /**
         * @private
         */
        _destroy: function () {
            this.element.jstree('destroy');

            if (this.buttons) {
                this.buttons.forEach(function (element) {
                    if (element && element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                });
            }
        },

        /**
         * @private
         */
        _bind: function () {
            this.element.on('select_node.jstree', $.proxy(this._selectChildNodes, this));
            this.element.on('deselect_node.jstree', $.proxy(this._deselectChildNodes, this));
            this.element.on('changed.jstree', $.proxy(this._changedNode, this));
        },

        /**
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _selectChildNodes: function (event, selected) {
            selected.instance.open_node(selected.node);
            selected.node.children.each(function (id) {
                var selector = '[id="' + id + '"]';

                selected.instance.select_node(
                    selected.instance.get_node($(selector), false)
                );
            });
        },

        /**
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _deselectChildNodes: function (event, selected) {
            selected.node.children.each(function (id) {
                var selector = '[id="' + id + '"]';

                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                selected.instance.deselect_node(
                    selected.instance.get_node($(selector), false)
                );
                // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            });
        },

        /**
         * Add selected resources to form to be send later
         *
         * @param {Event} event
         * @param {Object} selected
         * @private
         */
        _changedNode: function (event, selected) {
            var form = $(this.options.editFormSelector),
                fieldName = this.options.resourceFieldName,
                items = selected.selected.concat($(this.element).jstree('get_undetermined'));

            if (this.options.editFormSelector === '') {
                return;
            }
            form.find('input[name="' + this.options.resourceFieldName +  '"]').remove();
            items.each(function (id) {
                $('<input>', {
                    type: 'hidden',
                    name: fieldName,
                    value: id
                }).appendTo(form);
            });
        }
    });

    return $.mage.rolesTree;
});
