/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiComponent',
    'Magento_Ui/js/lib/view/utils/raf'
], function ($, _, Component, raf) {
    'use strict';

    return Component.extend({
        defaults: {
            listingSelector: '${ $.listingProvider }::not([data-role = "sticky-el-root"])',
            toolbarSelector: '${ $.toolbarProvider }::not([data-role = "sticky-el-root"])',
            bulkRowSelector: '[data-role = "data-grid-bulk-row"]',
            bulkRowHeaderSelector: '.data-grid-info-panel:visible',
            tableSelector: 'table',
            columnSelector: 'thead tr th',
            rowSelector: 'tbody tr',
            toolbarCollapsiblesSelector: '[data-role="toolbar-menu-item"]',
            toolbarCollapsiblesActiveClass: '_active',
            template: 'ui/grid/sticky/sticky',
            stickyContainerSelector: '.sticky-header',
            stickyElementSelector: '[data-role = "sticky-el-root"]',
            leftDataGridCapSelector: '.data-grid-cap-left',
            rightDataGridCapSelector: '.data-grid-cap-right',
            visible: false,
            enableToolbar: true,
            enableHeader: true,
            modules: {
                toolbar: '${ $.toolbarProvider }',
                listing: '${ $.listingProvider }'
            },
            otherStickyElsSize: 77,
            containerNode: null,
            listingNode: null,
            toolbarNode: null,
            stickyListingNode: null,
            stickyToolbarNode: null,
            storedOriginalToolbarElements: [],
            cache: {},
            flags: {},
            dirtyFlag: 'dirty'
        },

        /**
         * Initializes Sticky component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            _.bindAll(this,
                'adjustStickyElems',
                'initListingNode',
                'initToolbarNode',
                'initContainerNode',
                'initColumns',
                'initStickyListingNode',
                'initStickyToolbarNode',
                'initLeftDataGridCap',
                'initRightDataGridCap'
            );

            $.async(this.listingSelector,
                this.initListingNode);
            $.async(this.toolbarSelector,
                this.initToolbarNode);

            $.async(this.stickyContainerSelector,
                this,
                this.initContainerNode);

            return this;
        },

        /**
         * Init observables
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('visible');

            return this;
        },

        /**
         * Init original listing node
         *
         * @param {HTMLElement} node
         */
        initListingNode: function (node) {
            if ($(node).is(this.stickyElementSelector)) {
                return;
            }
            this.listingNode = $(node);
            $.async(this.columnSelector, node, this.initColumns);
        },

        /**
         * Init original toolbar node
         *
         * @param {HTMLElement} node
         */
        initToolbarNode: function (node) {
            if ($(node).is(this.stickyElementSelector)) {
                return;
            }
            this.toolbarNode = $(node);
        },

        /**
         * Init sticky listing node
         *
         * @param {HTMLElement} node
         */
        initStickyListingNode: function (node) {
            this.stickyListingNode = $(node);
            this.checkPos();
            this.initListeners();
        },

        /**
         * Init sticky toolbar node
         *
         * @param {HTMLElement} node
         */
        initStickyToolbarNode: function (node) {
            this.stickyToolbarNode = $(node);
        },

        /**
         * Init sticky header container node
         *
         * @param {HTMLElement} node
         */
        initContainerNode: function (node) {
            this.containerNode = $(node);

            $.async(this.leftDataGridCapSelector,
                node,
                this.initLeftDataGridCap);
            $.async(this.rightDataGridCapSelector,
                node,
                this.initRightDataGridCap);

            $.async(this.stickyElementSelector,
                this.listing(),
                this.initStickyListingNode);
            $.async(this.stickyElementSelector,
                this.toolbar(),
                this.initStickyToolbarNode);
        },

        /**
         * Init columns (each time when amount of columns is changed)
         *
         */
        initColumns: function () {
            this.columns = this.listingNode.find(this.columnSelector);
        },

        /**
         * Init left DataGridCap
         *
         * @param {HTMLElement} node
         */
        initLeftDataGridCap: function (node) {
            this.leftDataGridCap = $(node);
        },

        /**
         * Init right DataGridCap
         *
         * @param {HTMLElement} node
         */
        initRightDataGridCap: function (node) {
            this.rightDataGridCap = $(node);
        },

        /**
         * Init listeners
         *
         * @returns {Object} Chainable.
         */
        initListeners: function () {
            this.adjustStickyElems();
            this.initOnResize()
                .initOnScroll()
                .initOnListingScroll();

            return this;
        },

        /**
         * Start to listen to window scroll event
         *
         * @returns {Object} Chainable.
         */
        initOnScroll: function () {
            this.lastHorizontalScrollPos = $(window).scrollLeft();
            document.addEventListener('scroll', function () {
                this.flags.scrolled = true;
            }.bind(this));

            return this;
        },

        /**
         * Start to listen to original listing scroll event
         *
         * @returns {Object} Chainable.
         */
        initOnListingScroll: function () {
            $(this.listingNode).scroll(function (e) {
                this.flags.listingScrolled = true;
                this.flags.listingScrolledValue = $(e.target).scrollLeft();
            }.bind(this));

            return this;
        },

        /**
         * Start to listen to window resize event
         *
         * @returns {Object} Chainable.
         */
        initOnResize: function () {
            $(window).resize(function () {
                this.flags.resized = true;
            }.bind(this));

            return this;
        },

        /**
         * Adjust sticky header elements according to flags of the events that have happened in the endless RAF loop
         */
        adjustStickyElems: function () {
            if (this.flags.resized ||
                this.flags.scrolled) {
                this.checkPos();
            }

            if (this.visible) {
                this.checkTableElemsWidth();

                if (this.flags.originalWidthChanged) {
                    this.adjustContainerElemsWidth();
                }

                if (this.flags.resized) {
                    this.onResize();
                }

                if (this.flags.scrolled) {
                    this.onWindowScroll();
                }

                if (this.flags.listingScrolled) {
                    this.onListingScroll(this.flags.listingScrolledValue);
                }
            }
            _.each(this.flags, function (val, key) {
                if (val === this.dirtyFlag) {
                    this.flags[key] = false;
                } else if (val) {
                    this.flags[key] = this.dirtyFlag;
                }
            }, this);

            raf(this.adjustStickyElems);
        },

        /**
         * Handles window scroll
         */
        onWindowScroll: function () {
            var scrolled = $(window).scrollLeft(),
                horizontal = this.lastHorizontalScrollPos !== scrolled;

            if (horizontal) {
                this.adjustOffset()
                    .adjustDataGridCapPositions();
                this.lastHorizontalScrollPos = scrolled;
            } else {
                this.checkPos();
            }
        },

        /**
         * Handles original listing scroll
         *
         * @param {Number} scrolled
         */
        onListingScroll: function (scrolled) {
            this.adjustOffset(scrolled);
        },

        /**
         * Handles window resize
         */
        onResize: function () {
            this.checkPos();
            this.adjustContainerElemsWidth()
                .adjustDataGridCapPositions();
        },

        /**
         * Check if original table or columns change it dimensions and sets appropriate flag
         */
        checkTableElemsWidth: function () {
            var newWidth = this.getTableWidth();

            if (this.cache.tableWidth !== newWidth) {
                this.cache.tableWidth = newWidth;
                this.flags.originalWidthChanged = true;
            } else if (this.cache.colChecksum !== this.getColsChecksum()) {
                this.cache.colChecksum = this.getColsChecksum();
                this.flags.originalWidthChanged = true;
            }
        },

        /**
         * Get the checksum of original columns width
         *
         * @returns {Number}.
         */
        getColsChecksum: function () {
            return _.reduce(this.columns,
            function (pv, cv) {
                return ($(pv).width() || pv) + '' + $(cv).width();
            });
        },

        /**
         * Get the width of the sticky table wrapper
         *
         * @returns {Number}.
         */
        getListingWidth: function () {
            return this.listingNode.width();
        },

        /**
         * Get the width of the original table
         *
         * @returns {Number}.
         */
        getTableWidth: function () {
            return this.listingNode.find(this.tableSelector).width();
        },

        /**
         * Get the top elem: header or toolbar
         *
         * @returns {HTMLElement}.
         */
        getTopElement: function () {
            return this.toolbarNode || this.listingNode;
        },

        /**
         * Get the height of the other sticky elem (Page header)
         *
         * @returns {Number}.
         */
        getOtherStickyElementsSize: function () {
            return this.otherStickyElsSize;
        },

        /**
         * Get original bulk row height, if is visible
         *
         * @returns {Number}.
         */
        getBulkRowHeight: function () {
            return this.listingNode.find(this.bulkRowSelector).filter(':visible').height();
        },

        /**
         * Get top Y coord of the sticky header
         *
         * @returns {Number}.
         */
        getListingTopYCoord: function () {
            var bulkRowHeight = this.getBulkRowHeight();

            return this.listingNode.find('tbody').offset().top -
                this.containerNode.height() -
                $(window).scrollTop() +
                bulkRowHeight;
        },

        /**
         * Check if sticky header must be visible
         *
         * @returns {Boolean}.
         */
        getMustBeSticky: function () {
            var stickyTopCondition = this.getListingTopYCoord() - this.getOtherStickyElementsSize(),
                stickyBottomCondition = this.listingNode.offset().top +
                    this.listingNode.height() -
                    $(window).scrollTop() +
                    this.getBulkRowHeight() -
                    this.getOtherStickyElementsSize();

            return stickyTopCondition < 0 && stickyBottomCondition > 0;
        },

        /**
         * Resize sticky header and cols
         *
         * @returns {Object} Chainable.
         */
        adjustContainerElemsWidth: function () {
            this.resizeContainer()
                .resizeCols()
                .resizeBulk();

            return this;
        },

        /**
         * Resize sticky header
         *
         * @returns {Object} Chainable.
         */
        resizeContainer: function () {
            var listingWidth = this.getListingWidth();

            this.stickyListingNode.innerWidth(listingWidth);
            this.stickyListingNode.find(this.tableSelector).innerWidth(this.getTableWidth());

            if (this.stickyToolbarNode) {
                this.stickyToolbarNode.innerWidth(listingWidth);
            }

            return this;
        },

        /**
         * Resize sticky cols
         *
         * @returns {Object} Chainable.
         */
        resizeCols: function () {
            var cols = this.listingNode.find(this.columnSelector);

            this.stickyListingNode.find(this.columnSelector).each(function (ind) {
                var originalColWidth =  $(cols[ind]).width();

                $(this).width(originalColWidth);
            });

            return this;
        },

        /**
         * Resize bulk row header
         *
         * @returns {Object} Chainable.
         */
        resizeBulk: function () {
            var bulk = this.containerNode.find(this.bulkRowHeaderSelector)[0];

            if (bulk) {
                $(bulk).innerWidth(this.getListingWidth());
            }

            return this;
        },

        /**
         * Reset viewport to the top of listing
         */
        resetToTop: function () {
            var posOfTopEl = this.getTopElement().offset().top - this.getOtherStickyElementsSize() || 0;

            $(window).scrollTop(posOfTopEl);
        },

        /**
         * Adjust sticky header offset
         *
         * @param {Number} val
         * @returns {Object} Chainable.
         */
        adjustOffset: function (val) {
            val = val || this.listingNode.scrollLeft();
            this.stickyListingNode.offset({
                left: this.listingNode.offset().left - val
            });

            return this;
        },

        /**
         * Adjust both DataGridCap position
         *
         * @returns {Object} Chainable.
         */
        adjustDataGridCapPositions: function () {
            this.adjustLeftDataGridCapPos()
                .adjustRightDataGridCapPos();

            return this;
        },

        /**
         * Adjust left DataGridCap position
         *
         * @returns {Object} Chainable.
         */
        adjustLeftDataGridCapPos: function () {
            this.leftDataGridCap.offset({
                left: this.listingNode.offset().left - this.leftDataGridCap.width()
            });

            return this;
        },

        /**
         * Adjust right DataGridCap position
         *
         * @returns {Object} Chainable.
         */
        adjustRightDataGridCapPos: function () {
            this.rightDataGridCap.offset({
                left: this.listingNode.offset().left + this.listingNode.width()
            });

            return this;
        },

        /**
         * Hides the oiginal toolbar opened dropdowns/collapsibles etc
         */
        collapseOriginalElements: function () {
            this.toolbarNode
                .find(this.toolbarCollapsiblesSelector)
                .css('visibility', 'hidden');
            $(this.listingNode.find(this.bulkRowSelector)[0]).css('visibility', 'hidden');
        },

        /**
         * Restores the oiginal toolbar opened dropdowns/collapsibles etc
         */
        restoreOriginalElements: function () {
            this.toolbarNode
                .find(this.toolbarCollapsiblesSelector)
                .css('visibility', 'visible');
            $(this.listingNode.find(this.bulkRowSelector)[0]).css('visibility', 'visible');
        },

        /**
         * Toggle the visibility of sticky header
         *
         * @returns {Object} Chainable.
         */
        toggleContainerVisibility: function () {
            this.visible = !this.visible;

            return this;
        },

        /**
         * Checks position of the listing to know if need to show/hide sticky header
         *
         * @returns {Boolean} whether the visibility of the sticky header was toggled.
         */
        checkPos: function () {
            var isSticky = this.visible,
                mustBeSticky = this.getMustBeSticky(),
                needChange = isSticky !== mustBeSticky;

            if (needChange) {
                if (mustBeSticky) {
                    this.collapseOriginalElements();
                    this.toggleContainerVisibility();
                    this.adjustContainerElemsWidth()
                        .adjustOffset()
                        .adjustDataGridCapPositions();

                } else {
                    this.toggleContainerVisibility();
                    this.restoreOriginalElements();
                }
            }

            return needChange;
        }
    });
});
