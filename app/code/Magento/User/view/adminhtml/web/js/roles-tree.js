/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/jstree/jquery.jstree'
], function ($) {
    'use strict';

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
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    three_state: false,
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
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
        },

        /**
         * @private
         */
        _destroy: function () {
            this.element.jstree('destroy');
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
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            selected.instance.open_node(selected.node);
            selected.node.children.each(function (id) {
                var selector = '[id="' + id + '"]';

                selected.instance.select_node(
                    selected.instance.get_node($(selector), false)
                );
            });
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
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
