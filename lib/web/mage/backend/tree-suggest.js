/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui',
            'jquery/jstree/jquery.jstree',
            'mage/backend/suggest'
        ], factory);
    } else {
        factory(root.jQuery);
    }
}(this, function ($) {
    'use strict';

    /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
    var hover_node, dehover_node, select_node, init;

    $.extend(true, $, {
        // @TODO: Move method 'treeToList' in file with utility functions
        mage: {
            /**
             * @param {Array} list
             * @param {Object} nodes
             * @param {*} level
             * @param {*} path
             * @return {*}
             */
            treeToList: function (list, nodes, level, path) {
                $.each(nodes, function () {
                    if ($.type(this) === 'object') {
                        list.push({
                            label: this.label,
                            id: this.id,
                            level: level,
                            item: this,
                            path: path + this.label
                        });

                        if (this.children) {
                            $.mage.treeToList(list, this.children, level + 1, path + this.label + ' / ');
                        }
                    }
                });

                return list;
            }
        }
    });

    hover_node = $.jstree._instance.prototype.hover_node;
    dehover_node = $.jstree._instance.prototype.dehover_node;
    select_node = $.jstree._instance.prototype.select_node;
    init = $.jstree._instance.prototype.init;

    $.extend(true, $.jstree._instance.prototype, {
        /**
         * @override
         */
        init: function () {
            this.get_container()
                .show()
                .on('keydown', $.proxy(function (e) {
                    var o;

                    if (e.keyCode === $.ui.keyCode.ENTER) {
                        o = this.data.ui.hovered || this.data.ui.last_selected || -1;
                        this.select_node(o, true);
                    }
                }, this));
            init.call(this);
        },

        /**
         * @override
         */
        hover_node: function (obj) {
            hover_node.apply(this, arguments);
            obj = this._get_node(obj);

            if (!obj.length) {
                return false;
            }
            this.get_container().trigger('hover_node', [{
                item: obj.find('a:first')
            }]);
        },

        /**
         * @override
         */
        dehover_node: function () {
            dehover_node.call(this);
            this.get_container().trigger('dehover_node');
        },

        /**
         * @override
         */
        select_node: function (o) {
            var node;

            select_node.apply(this, arguments);
            node = this._get_node(o);

            (node ? $(node) : this.data.ui.last_selected)
                .trigger('select_tree_node');
        }
    });

    $.widget('mage.treeSuggest', $.mage.suggest, {
        widgetEventPrefix: 'suggest',
        options: {
            template:
                '<% if (data.items.length) { %>' +
                    '<% if (data.allShown()) { %>' +
                        '<% if (typeof data.nested === "undefined") { %>' +
                            '<div style="display:none;" data-mage-init="{&quot;jstree&quot;:{&quot;plugins&quot;:[&quot;themes&quot;,&quot;html_data&quot;,&quot;ui&quot;,&quot;hotkeys&quot;],&quot;themes&quot;:{&quot;theme&quot;:&quot;default&quot;,&quot;dots&quot;:false,&quot;icons&quot;:false}}}">' + //eslint-disable-line max-len
                        '<% } %>' +
                        '<ul>' +
                            '<% _.each(data.items, function(value) { %>' +
                                '<li class="<% if (data.itemSelected(value)) { %>mage-suggest-selected<% } %>' +
                '                   <% if (value.is_active == 0) { %> mage-suggest-not-active<% } %>">' +
                                    '<a href="#" <%= data.optionData(value) %>><%- value.label %></a>' +
                                    '<% if (value.children && value.children.length) { %>' +
                                        '<%= data.renderTreeLevel(value.children) %>' +
                                    '<% } %>' +
                                '</li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<% if (typeof data.nested === "undefined") { %>' +
                            '</div>' +
                        '<% } %>' +
                    '<% } else { %>' +
                        '<ul data-mage-init="{&quot;menu&quot;:[]}">' +
                            '<% _.each(data.items, function(value) { %>' +
                                '<% if (!data.itemSelected(value)) {%>' +
                                    '<li <%= data.optionData(value) %>>' +
                                        '<a href="#">' +
                                            '<span class="category-label"><%- value.label %></span>' +
                                            '<span class="category-path"><%- value.path %></span>' +
                                        '</a>' +
                                    '</li>' +
                                '<% } %>' +
                            '<% }); %>' +
                        '</ul>' +
                    '<% } %>' +
                '<% } else { %>' +
                    '<span class="mage-suggest-no-records"><%- data.noRecordsText %></span>' +
                '<% } %>',
            controls: {
                selector: ':ui-menu, :mage-menu, .jstree',
                eventsMap: {
                    focus: ['menufocus', 'hover_node'],
                    blur: ['menublur', 'dehover_node'],
                    select: ['menuselect', 'select_tree_node']
                }
            }
        },

        /**
         * @override
         */
        _bind: function () {
            this._super();
            this._on({
                /**
                 * @param {jQuery.Event} event
                 */
                keydown: function (event) {
                    var keyCode = $.ui.keyCode;

                    switch (event.keyCode) {
                        case keyCode.LEFT:

                        case keyCode.RIGHT:

                            if (this.isDropdownShown()) {
                                event.preventDefault();
                                this._proxyEvents(event);
                            }
                            break;
                    }
                }
            });
        },

        /**
         * @override
         */
        close: function (e) {
            var eType = e ? e.type : null;

            if (eType === 'select_tree_node') {
                this.element.focus();
            } else {
                this._superApply(arguments);
            }
        },

        /**
         * @override
         */
        _filterSelected: function (items, context) {
            if (context._allShown) {
                return items;
            }

            return this._superApply(arguments);
        },

        /**
         * @override
         */
        _prepareDropdownContext: function () {
            var context = this._superApply(arguments),
                optionData = context.optionData,
                templates = this.templates,
                tmplName = this.templateName;

            /**
             * @param {Object} item
             * @return {*|String}
             */
            context.optionData = function (item) {
                item = $.extend({}, item);
                delete item.children;

                return optionData(item);
            };

            return $.extend(context, {
                /**
                 * @param {*} children
                 * @return {*|jQuery}
                 */
                renderTreeLevel: function (children) {
                    var _context = $.extend({}, this, {
                        items: children,
                        nested: true
                    }),
                    tmpl = templates[tmplName];

                    tmpl = tmpl({
                        data: _context
                    });

                    return $('<div>').append($(tmpl)).html();
                }
            });
        },

        /**
         * @override
         */
        _processResponse: function (e, items, context) {
            var control;

            if (context && !context._allShown) {
                items = this.filter($.mage.treeToList([], items, 0, ''), this._term);
            }
            control = this.dropdown.find(this._control.selector);

            if (control.length && control.hasClass('jstree')) {
                control.jstree('destroy');
            }
            this._superApply([e, items, context]);
        }
    });

    return $.mage.treeSuggest;
}));
