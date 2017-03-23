/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Ui/js/lib/view/utils/raf',
    'rjsResolver',
    'uiCollection'
], function (_, $, raf, resolver, Collection) {
    'use strict';

    var transformProp;

    /**
     * Defines supported css 'transform' property.
     *
     * @returns {String|Undefined}
     */
    transformProp = (function () {
        var style = document.documentElement.style,
            base = 'Transform',
            vendors = ['webkit', 'moz', 'ms', 'o'],
            vi = vendors.length,
            property;

        if (typeof style.transform != 'undefined') {
            return 'transform';
        }

        while (vi--) {
            property = vendors[vi] + base;

            if (typeof style[property] != 'undefined') {
                return property;
            }
        }
    })();

    /**
     * Moves specified DOM element to the x and y coordinates.
     *
     * @param {HTMLElement} elem - Element to be relocated.
     * @param {Number} x - X coordinate.
     * @param {Number} y - Y coordinate.
     */
    function locate(elem, x, y) {
        var value = 'translate(' + x + 'px,' + y + 'px)';

        elem.style[transformProp] = value;
    }

    return Collection.extend({
        defaults: {
            template: 'ui/grid/toolbar',
            stickyTmpl: 'ui/grid/sticky/sticky',
            tableSelector: 'table',
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            refreshFPS: 15,
            sticky: false,
            visible: false,
            _resized: true,
            _scrolled: true,
            _tableScrolled: true,
            _requiredNodes: {
                '$stickyToolbar': true,
                '$stickyTable': true,
                '$table': true,
                '$sticky': true
            },
            stickyClass: {
                'sticky-header': true
            }
        },

        /**
         * Initializes sticky toolbar component.
         *
         * @returns {Sticky} Chainable.
         */
        initialize: function () {
            this._super();

            if (this.sticky) {
                this.waitDOMElements()
                    .then(this.run.bind(this));
            }

            return this;
        },

        /**
         * Establishes DOM elements wait process.
         *
         * @returns {jQueryPromise} Promise which will be resolved
         *      when all of the required DOM elements are defined.
         */
        waitDOMElements: function () {
            var _domPromise = $.Deferred();

            _.bindAll(this, 'setStickyTable', 'setTableNode');

            $.async({
                ctx: ':not([data-role="sticky-el-root"])',
                component: this.columnsProvider,
                selector: this.tableSelector
            }, this.setTableNode);

            $.async({
                ctx: '[data-role="sticky-el-root"]',
                component: this.columnsProvider,
                selector: this.tableSelector
            }, this.setStickyTable);

            this._domPromise = _domPromise;

            return _domPromise.promise();
        },

        /**
         * Defines left caption element.
         *
         * @param {HTMLElement} node
         */
        setLeftCap: function (node) {
            this.$leftCap = node;
        },

        /**
         * Defines right caption element.
         *
         * @param {HTMLElement} node
         */
        setRightCap: function (node) {
            this.$rightCap = node;
        },

        /**
         * Defines original table element.
         *
         * @param {HTMLTableElement} node
         */
        setTableNode: function (node) {
            this.$cols = node.tHead.children[0].cells;
            this.$tableContainer = node.parentNode;

            this.setNode('$table', node);
        },

        /**
         * Defines sticky table element.
         *
         * @param {HTMLTableElement} node
         */
        setStickyTable: function (node) {
            this.$stickyCols = node.tHead.children[0].cells;

            this.setNode('$stickyTable', node);
        },

        /**
         * Defines sticky toolbar node.
         *
         * @param {HTMLElement} node
         */
        setStickyToolbarNode: function (node) {
            this.setNode('$stickyToolbar', node);
        },

        /**
         * Defines sticky element container.
         *
         * @param {HTMLElement} node
         */
        setStickyNode: function (node) {
            this.setNode('$sticky', node);
        },

        /**
         * Defines toolbar element container.
         *
         * @param {HTMLElement} node
         */
        setToolbarNode: function (node) {
            this.$toolbar = node;
        },

        /**
         * Sets provided node as a value of 'key' property and
         * performs check for required DOM elements.
         *
         * @param {String} key - Properties key.
         * @param {HTMLElement} node - DOM element.
         */
        setNode: function (key, node) {
            var nodes = this._requiredNodes,
                promise = this._domPromise,
                defined;

            this[key] = node;

            defined = _.every(nodes, function (enabled, name) {
                return enabled ? this[name] : true;
            }, this);

            if (defined) {
                resolver(promise.resolve, promise);
            }
        },

        /**
         * Starts refresh process of the sticky element
         * and assigns DOM elements events handlers.
         */
        run: function () {
            _.bindAll(
                this,
                'refresh',
                '_onWindowResize',
                '_onWindowScroll',
                '_onTableScroll'
            );

            $(window).on({
                scroll: this._onWindowScroll,
                resize: this._onWindowResize
            });

            $(this.$tableContainer).on('scroll', this._onTableScroll);

            this.refresh();
            this.checkTableWidth();
        },

        /**
         * Refreshes state of the sticky element and
         * invokes DOM elements events handlers
         * if corresponding event has been triggered.
         */
        refresh: function () {
            if (!raf(this.refresh, this.refreshFPS)) {
                return;
            }

            if (this._scrolled) {
                this.onWindowScroll();
            }

            if (this._tableScrolled) {
                this.onTableScroll();
            }

            if (this._resized) {
                this.onWindowResize();
            }

            if (this.visible) {
                this.checkTableWidth();
            }
        },

        /**
         * Shows sticky toolbar.
         *
         * @returns {Sticky} Chainable.
         */
        show: function () {
            this.visible = true;

            this.$sticky.style.display = '';
            this.$toolbar.style.visibility = 'hidden';

            return this;
        },

        /**
         * Hides sticky toolbar.
         *
         * @returns {Sticky} Chainable.
         */
        hide: function () {
            this.visible = false;

            this.$sticky.style.display = 'none';
            this.$toolbar.style.visibility = '';

            return this;
        },

        /**
         * Checks if sticky toolbar covers original elements.
         *
         * @returns {Boolean}
         */
        isCovered: function () {
            var stickyTop = this._stickyTableTop + this._wScrollTop;

            return stickyTop > this._tableTop;
        },

        /**
         * Updates offset of the sticky table element.
         *
         * @returns {Sticky} Chainable.
         */
        updateStickyTableOffset: function () {
            var style,
                top;

            if (this.visible) {
                top = this.$stickyTable.getBoundingClientRect().top;
            } else {
                style = this.$sticky.style;

                style.visibility = 'hidden';
                style.display = '';

                top = this.$stickyTable.getBoundingClientRect().top;

                style.display = 'none';
                style.visibility = '';
            }

            this._stickyTableTop = top;

            return this;
        },

        /**
         * Updates offset of the original table element.
         *
         * @returns {Sticky} Chainable.
         */
        updateTableOffset: function () {
            var box = this.$table.getBoundingClientRect(),
                top = box.top + this._wScrollTop;

            if (this._tableTop !== top) {
                this._tableTop = top;

                this.onTableTopChange(top);
            }

            return this;
        },

        /**
         * Checks if width of the table or it's columns has changed.
         *
         * @returns {Sticky} Chainable.
         */
        checkTableWidth: function () {
            var cols        = this.$cols,
                total       = cols.length,
                rightBorder = cols[total - 2].offsetLeft,
                tableWidth  = this.$table.offsetWidth;

            if (this._tableWidth !== tableWidth) {
                this._tableWidth = tableWidth;

                this.onTableWidthChange(tableWidth);
            }

            if (this._rightBorder !== rightBorder) {
                this._rightBorder = rightBorder;

                this.onColumnsWidthChange();
            }

            return this;
        },

        /**
         * Updates width of the sticky table.
         *
         * @returns {Sticky} Chainable.
         */
        updateTableWidth: function () {
            this.$stickyTable.style.width = this._tableWidth + 'px';

            if (this._tableWidth < this._toolbarWidth) {
                this.checkToolbarSize();
            }

            return this;
        },

        /**
         * Updates width of the sticky columns.
         *
         * @returns {Sticky} Chainable.
         */
        updateColumnsWidth: function () {
            var cols        = this.$cols,
                index       = cols.length,
                stickyCols  = this.$stickyCols;

            while (index--) {
                stickyCols[index].width = cols[index].offsetWidth;
            }

            return this;
        },

        /**
         * Upadates size of the sticky toolbar element
         * and invokes corresponding 'change' event handlers.
         *
         * @returns {Sticky} Chainable.
         */
        checkToolbarSize: function () {
            var width = this.$tableContainer.offsetWidth;

            if (this._toolbarWidth !== width) {
                this._toolbarWidth = width;

                this.onToolbarWidthChange(width);
            }

            return this;
        },

        /**
         * Toggles sticky toolbar visibility if it's necessary.
         *
         * @returns {Sticky} Chainable.
         */
        updateVisibility: function () {
            if (this.visible !== this.isCovered()) {
                this.visible ? this.hide() : this.show();
            }

            return this;
        },

        /**
         * Updates position of the left cover area.
         *
         * @returns {Sticky} Chainable.
         */
        updateLeftCap: function () {
            locate(this.$leftCap, -this._wScrollLeft, 0);

            return this;
        },

        /**
         * Updates position of the right cover area.
         *
         * @returns {Sticky} Chainable.
         */
        updateRightCap: function () {
            var left = this._toolbarWidth - this._wScrollLeft;

            locate(this.$rightCap, left, 0);

            return this;
        },

        /**
         * Updates position of the sticky table.
         *
         * @returns {Sticky} Chainable.
         */
        updateTableScroll: function () {
            var container = this.$tableContainer,
                left = container.scrollLeft + this._wScrollLeft;

            locate(this.$stickyTable, -left, 0);

            return this;
        },

        /**
         * Updates width of the toolbar element.
         *
         * @returns {Sticky} Chainable.
         */
        updateToolbarWidth: function () {
            this.$stickyToolbar.style.width = this._toolbarWidth + 'px';

            return this;
        },

        /**
         * Handles changes of the toolbar element's width.
         */
        onToolbarWidthChange: function () {
            this.updateToolbarWidth()
                .updateRightCap();
        },

        /**
         * Handles changes of the table top position.
         */
        onTableTopChange: function () {
            this.updateStickyTableOffset();
        },

        /**
         * Handles change of the table width.
         */
        onTableWidthChange: function () {
            this.updateTableWidth();
        },

        /**
         * Handles change of the table columns width.
         */
        onColumnsWidthChange: function () {
            this.updateColumnsWidth();
        },

        /**
         * Handles changes of the window's size.
         */
        onWindowResize: function () {
            this.checkToolbarSize();

            this._resized = false;
        },

        /**
         * Handles changes of the original table scroll position.
         */
        onTableScroll: function () {
            this.updateTableScroll();

            this._tableScrolled = false;
        },

        /**
         * Handles changes of window's scroll position.
         */
        onWindowScroll: function () {
            var scrollTop = window.pageYOffset,
                scrollLeft = window.pageXOffset;

            if (this._wScrollTop !== scrollTop) {
                this._wScrollTop = scrollTop;

                this.onWindowScrollTop(scrollTop);
            }

            if (this._wScrollLeft !== scrollLeft) {
                this._wScrollLeft = scrollLeft;

                this.onWindowScrollLeft(scrollLeft);
            }

            this._scrolled = false;
        },

        /**
         * Handles changes of windows' top scroll postion.
         */
        onWindowScrollTop: function () {
            this.updateTableOffset()
                .updateVisibility();
        },

        /**
         * Handles changes of windows' left scroll position.
         */
        onWindowScrollLeft: function () {
            this.updateRightCap()
                .updateLeftCap()
                .updateTableScroll();
        },

        /**
         * Original window 'scroll' event handler.
         * Sets 'scrolled' flag to 'true'.
         *
         * @private
         */
        _onWindowScroll: function () {
            this._scrolled = true;
        },

        /**
         * Original window 'resize' event handler.
         * Sets 'resized' flag to 'true'.
         *
         * @private
         */
        _onWindowResize: function () {
            this._resized = true;
        },

        /**
         * Original table 'scroll' event handler.
         * Sets '_tableScrolled' flag to 'true'.
         *
         * @private
         */
        _onTableScroll: function () {
            this._tableScrolled = true;
        }
    });
});
