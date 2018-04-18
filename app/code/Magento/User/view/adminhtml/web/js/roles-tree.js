/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/jstree/jquery.jstree"
], function($){
    'use strict';

    $.widget('mage.rolesTree', {
        options: {
            treeInitData: {},
            treeInitSelectedData: {}
        },
        _create: function() {
            this.element.jstree({
                plugins: ["themes", "json_data", "ui", "crrm", "types", "vcheckbox", "hotkeys"],
                vcheckbox: {
                    'two_state': true,
                    'real_checkboxes': true,
                    'real_checkboxes_names': function(n) {return ['resource[]', $(n).data('id')]}
                },
                json_data: {data: this.options.treeInitData},
                ui: {select_limit: 0},
                hotkeys: {
                    space: this._changeState,
                    'return': this._changeState
                },
                types: {
                    'types': {
                        'disabled': {
                            'check_node': false,
                            'uncheck_node': false
                        }
                    }
                }
            });
            this._bind();
        },
        _destroy: function() {
            this.element.jstree('destroy');
        },
        _bind: function() {
            this.element.on('loaded.jstree', $.proxy(this._checkNodes, this));
            this.element.on('click.jstree', 'a', $.proxy(this._checkNode, this));
        },
        _checkNode: function(event) {
            event.stopPropagation();
            this.element.jstree(
                'change_state',
                event.currentTarget,
                this.element.jstree('is_checked', event.currentTarget)
            );
        },
        _checkNodes: function() {
            var $items = $('[data-id="' + this.options.treeInitSelectedData.join('"],[data-id="') + '"]');
            $items.removeClass("jstree-unchecked").addClass("jstree-checked");
            $items.children(":checkbox").prop("checked", true);
        },
        _changeState: function() {
            if (this.data.ui.hovered) {
                var element = this.data.ui.hovered;
                this.change_state(element, this.is_checked(element));
            }
            return false;
        }
    });
    
    return $.mage.rolesTree;
});
