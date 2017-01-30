/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './abstract',
    'Magento_Ui/js/lib/key-codes',
    'mage/translate',
    'ko',
    'jquery',
    'Magento_Ui/js/lib/view/utils/async'
], function (_, Abstract, keyCodes, $t, ko, $) {
    'use strict';

    var isTouchDevice = typeof document.ontouchstart !== 'undefined';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    /**
     * Set levels to options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Number} level - Starting level
     * @param {String} path - path to root
     *
     * @returns {Array} Array with levels
     */
    function setProperty(array, separator, level, path) {
        var i = 0,
            length,
            nextLevel,
            nextPath;

        array = _.compact(array);
        length = array.length;
        level = level || 0;
        path = path || '';

        for (i; i < length; i++) {
            if (array[i]) {
                _.extend(array[i], {
                    level: level,
                    path: path
                });
            }

            if (array[i].hasOwnProperty(separator)) {
                nextLevel = level + 1;
                nextPath = path ? path + '.' + array[i].label : array[i].label;
                setProperty.call(this, array[i][separator], separator, nextLevel, nextPath);
            }
        }

        return array;
    }

    /**
     * Preprocessing options list
     *
     * @param {Array} nodes - Options list
     *
     * @return {Object} Object with property - options(options list)
     *      and cache options with plain and tree list
     */
    function parseOptions(nodes) {
        var caption,
            value,
            cacheNodes,
            copyNodes;

        nodes = setProperty(nodes, 'optgroup');
        copyNodes = JSON.parse(JSON.stringify(nodes));
        cacheNodes = flattenCollection(copyNodes, 'optgroup');

        nodes = _.map(nodes, function (node) {
            value = node.value;

            if (value == null || value === '') {
                if (_.isUndefined(caption)) {
                    caption = node.label;
                }
            } else {
                return node;
            }
        });

        return {
            options: _.compact(nodes),
            cacheOptions: {
                plain: _.compact(cacheNodes),
                tree: _.compact(nodes)
            }
        };
    }

    return Abstract.extend({
        defaults: {
            options: [],
            listVisible: false,
            value: [],
            filterOptions: false,
            chipsEnabled: true,
            itemsQuantity: '',
            filterInputValue: '',
            filterOptionsFocus: false,
            multiselectFocus: false,
            multiple: true,
            selectType: 'tree',
            lastSelectable: false,
            showFilteredQuantity: true,
            showCheckbox: true,
            levelsVisibility: true,
            openLevelsAction: true,
            showOpenLevelsActionIcon: true,
            optgroupLabels: false,
            closeBtn: true,
            showPath: true,
            labelsDecoration: false,
            disableLabel: false,
            filterRateLimit: 500,
            closeBtnLabel: $t('Done'),
            optgroupTmpl: 'ui/grid/filters/elements/ui-select-optgroup',
            quantityPlaceholder: $t('options'),
            hoverClass: '_hover',
            rootListSelector: 'ul.admin__action-multiselect-menu-inner._root',
            visibleOptionSelector: 'li.admin__action-multiselect-menu-inner-item:visible',
            actionTargetSelector: '.action-menu-item',
            selectedPlaceholders: {
                defaultPlaceholder: $t('Select...'),
                lotPlaceholders: $t('Selected')
            },
            hoverElIndex: null,
            separator: 'optgroup',
            listens: {
                listVisible: 'cleanHoveredElement',
                filterInputValue: 'filterOptionsList',
                options: 'checkOptionsList'
            },
            presets: {
                single: {
                    showCheckbox: false,
                    chipsEnabled: false,
                    closeBtn: false
                },
                optgroup: {
                    showCheckbox: false,
                    lastSelectable: true,
                    optgroupLabels: true,
                    openLevelsAction: false,
                    labelsDecoration: true,
                    showOpenLevelsActionIcon: false
                }
            }
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            this._super();

            $.async(
                this.rootListSelector,
                this,
                this.onRootListRender.bind(this)
            );

            return this;
        },

        /**
         * Parses options and merges the result with instance
         * Set defaults according to mode and levels configuration
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            var result = parseOptions(config.options),
                defaults = this.constructor.defaults,
                multiple = _.isBoolean(config.multiple) ? config.multiple : defaults.multiple,
                type = config.selectType || defaults.selectType,
                showOpenLevelsActionIcon = _.isBoolean(config.showOpenLevelsActionIcon) ?
                    config.showOpenLevelsActionIcon :
                    defaults.showOpenLevelsActionIcon,
                openLevelsAction = _.isBoolean(config.openLevelsAction) ?
                    config.openLevelsAction :
                    defaults.openLevelsAction;

            multiple = !multiple ? 'single' : false;
            config.showOpenLevelsActionIcon = showOpenLevelsActionIcon && openLevelsAction;
            _.extend(config, result, defaults.presets[multiple], defaults.presets[type]);
            this._super();

            return this;
        },

        /**
         * Check child optgroup
         */
        hasChildList: function () {
            return _.find(this.options(), function (option) {
                return !!option[this.separator];
            }, this);
        },

        /**
         * Check tree mode
         */
        isTree: function () {
            return this.hasChildList() && this.selectType !== 'optgroup';
        },

        /**
         * Add option to lastOptions array
         *
         * @param {Object} data
         * @returns {Boolean}
         */
        addLastElement: function (data) {
            if (!data.hasOwnProperty(this.separator)) {
                !this.cacheOptions.lastOptions ? this.cacheOptions.lastOptions = [] : false;

                if (!_.findWhere(this.cacheOptions.lastOptions, {value: data.value})) {
                    this.cacheOptions.lastOptions.push(data);
                }

                return true;
            }

            return false;
        },

        /**
         * Check options length and set to cache
         * if some options is added
         *
         * @param {Array} options - ui select options
         */
        checkOptionsList: function (options) {
            if (options.length > this.cacheOptions.plain.length) {
                this.cacheOptions.plain = options;
                this.setCaption();
            }
        },

        /**
         * Check label decoration
         */
        isLabelDecoration: function (data) {
            return data.hasOwnProperty(this.separator) && this.labelsDecoration;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe([
                'listVisible',
                'hoverElIndex',
                'placeholder',
                'multiselectFocus',
                'options',
                'itemsQuantity',
                'filterInputValue',
                'filterOptionsFocus'
            ]);

            this.filterInputValue.extend({rateLimit: this.filterRateLimit});

            return this;
        },

        /**
         * object with key - keyname and value - handler function for this key
         *
         * @returns {Object} Object with handlers function name.
         */
        keyDownHandlers: function () {
            return {
                enterKey: this.enterKeyHandler,
                escapeKey: this.escapeKeyHandler,
                spaceKey: this.enterKeyHandler,
                pageUpKey: this.pageUpKeyHandler,
                pageDownKey: this.pageDownKeyHandler
            };
        },

        /**
         * Processing level visibility for levels
         *
         * @param {Object} data - element data
         *
         * @returns {Boolean} level visibility.
         */
        showLevels: function (data) {
            var curLevel = ++data.level,
                isVisible;

            if (data.visible) {
                isVisible = data.visible();
            } else {
                isVisible = !!data.hasOwnProperty(this.separator) &&
                    _.isBoolean(this.levelsVisibility) &&
                    this.levelsVisibility ||
                    data.hasOwnProperty(this.separator) && parseInt(this.levelsVisibility, 10) >= curLevel;

                data.visible = ko.observable(isVisible);
                data.isVisited = isVisible;
            }

            return isVisible;
        },

        /**
         * Processing level visibility for levels
         *
         * @param {Object} data - element data
         *
         * @returns {Boolean} level visibility.
         */
        getLevelVisibility: function (data) {
            if (data.visible) {
                return data.visible();
            }

            return this.showLevels(data);
        },

        /**
         * Set option to options array.
         *
         * @param {Object} option
         * @param {Array} options
         */
        setOption: function (option, options) {
            var copyOptionsTree;

            options = options || this.cacheOptions.tree;

            _.each(options, function (opt) {
                if (opt.value == option.parent) { /* eslint eqeqeq:0 */
                    delete  option.parent;
                    opt[this.separator] ? opt[this.separator].push(option) : opt[this.separator] = [option];
                    copyOptionsTree = JSON.parse(JSON.stringify(this.cacheOptions.tree));
                    this.cacheOptions.plain = flattenCollection(copyOptionsTree, this.separator);
                    this.options(this.cacheOptions.tree);
                } else if (opt[this.separator]) {
                    this.setOption(option, opt[this.separator]);
                }
            }, this);
        },

        /**
         * Handler outerClick event. Closed options list
         */
        outerClick: function () {
            this.listVisible() ? this.listVisible(false) : false;

            if(isTouchDevice) {
               this.multiselectFocus(false);
            }
        },

        /**
         * Handler keydown event to filter options input
         *
         * @returns {Boolean} Returned true for emersion events
         */
        filterOptionsKeydown: function (data, event) {
            var key = keyCodes[event.keyCode];

            !this.isTabKey(event) ? event.stopPropagation() : false;

            if (key === 'pageDownKey' || key === 'pageUpKey') {
                event.preventDefault();
                this.filterOptionsFocus(false);
                this.cacheUiSelect.focus();
            }

            this.keydownSwitcher(data, event);

            return true;
        },

        /**
         * Filtered options list by value from filter options list
         */
        filterOptionsList: function () {
            var value = this.filterInputValue().trim().toLowerCase(),
                array = [];

            if (value && value.length < 2) {
                return false;
            }

            this.cleanHoveredElement();

            if (!value) {
                this.renderPath = false;
                this.options(this.cacheOptions.tree);
                this._setItemsQuantity(false);

                return false;
            }

            this.showPath ? this.renderPath = true : false;

            if (this.filterInputValue()) {

                array = this.selectType === 'optgroup' ?
                    this._getFilteredArray(this.cacheOptions.lastOptions, value) :
                    this._getFilteredArray(this.cacheOptions.plain, value);

                if (!value.length) {
                    this.options(this.cacheOptions.plain);
                    this._setItemsQuantity(this.cacheOptions.plain.length);
                } else {
                    this.options(array);
                    this._setItemsQuantity(array.length);
                }

                return false;
            }

            this.options(this.cacheOptions.plain);
        },

        /**
         * Filtered options list by value from filter options list
         *
         * @param {Array} list - option list
         * @param {String} value
         *
         * @returns {Array} filters result
         */
        _getFilteredArray: function (list, value) {
            var i = 0,
                array = [],
                curOption;

            for (i; i < list.length; i++) {
                curOption = list[i].label.toLowerCase();

                if (curOption.indexOf(value) > -1) {
                    array.push(list[i]); /*eslint max-depth: [2, 4]*/
                }
            }

            return array;
        },

        /**
         * Get path to current option
         *
         * @param {Object} data - option data
         * @returns {String} path
         */
        getPath: function (data) {
            var pathParts,
                createdPath = '';

            if (this.renderPath) {
                pathParts = data.path.split('.');
                _.each(pathParts, function (curData) {
                    createdPath = createdPath ? createdPath + ' / ' + curData : curData;
                });

                return createdPath;
            }
        },

        /**
         * Set filtered items quantity
         *
         * @param {Object} data - option data
         */
        _setItemsQuantity: function (data) {
            if (this.showFilteredQuantity) {
                data || parseInt(data, 10) === 0 ?
                    this.itemsQuantity(data + ' ' + this.quantityPlaceholder) :
                    this.itemsQuantity('');
            }
        },

        /**
         * Remove element from selected array
         */
        removeSelected: function (value, data, event) {
            event ? event.stopPropagation() : false;
            this.value.remove(value);
        },

        /**
         * Checked key name
         *
         * @returns {Boolean}
         */
        isTabKey: function (event) {
            return keyCodes[event.keyCode] === 'tabKey';
        },

        /**
         * Clean hoverElIndex variable
         *
         * @returns {Object} Chainable
         */
        cleanHoveredElement: function () {
            if (this.hoveredElement) {
                $(this.hoveredElement)
                    .children(this.actionTargetSelector)
                    .removeClass(this.hoverClass);

                this.hoveredElement = null;
            }

            return this;
        },

        /**
         * Check selected option
         *
         * @param {String} value - option value
         * @return {Boolean}
         */
        isSelected: function (value) {
            return this.multiple ? _.contains(this.value(), value) : this.value() === value;
        },

        /**
         * Check optgroup label
         *
         * @param {Object} data - element data
         * @return {Boolean}
         */
        isOptgroupLabels: function (data) {
            return data.hasOwnProperty(this.separator) && this.optgroupLabels;
        },

        /**
         * Check hovered option
         *
         * @param {Object} data - element data
         * @return {Boolean}
         */
        isHovered: function (data) {
            var element = this.hoveredElement,
                elementData;

            if (!element) {
                return false;
            }

            elementData = ko.dataFor(this.hoveredElement);

            return data.value === elementData.value;
        },

        /**
         * Toggle list visibility
         *
         * @returns {Object} Chainable
         */
        toggleListVisible: function () {
            this.listVisible(!this.listVisible());

            return this;
        },

        /**
         * Get selected element labels
         *
         * @returns {Array} array labels
         */
        getSelected: function () {
            var selected = this.value();

            return this.cacheOptions.plain.filter(function (opt) {
                return _.isArray(selected) ?
                    _.contains(selected, opt.value) :
                selected == opt.value;
            });
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!this.multiple) {
                if (!isSelected) {
                    this.value(data.value);
                }
                this.listVisible(false);
            } else {
                if (!isSelected) { /*eslint no-lonely-if: 0*/
                    this.value.push(data.value);
                } else {
                    this.value(_.without(this.value(), data.value));
                }
            }

            return this;
        },

        /**
         * Change visibility to child level
         *
         * @param {Object} data - element data
         */
        openChildLevel: function (data) {
            var contextElement = data,
                isVisible;

            if (
                this.openLevelsAction &&
                data.hasOwnProperty(this.separator) && _.isBoolean(this.levelsVisibility) ||
                this.openLevelsAction &&
                data.hasOwnProperty(this.separator) && parseInt(this.levelsVisibility, 10) <= data.level
            ) {
                isVisible = !contextElement.visible();

                if (isVisible && !contextElement.isVisited) {
                    contextElement.isVisited = true;
                }

                contextElement.visible(isVisible);
            }
        },

        /**
         * Check selected elements
         *
         * @returns {Boolean}
         */
        hasData: function () {
            if (!this.value()) {
                this.value([]);
            }

            return this.value() ? !!this.value().length : false;
        },

        /**
         * @deprecated
         */
        onMousemove: function () {},

        /**
         * Handles hover on list items.
         *
         * @param {Object} event - mousemove event
         */
        onDelegatedMouseMouve: function (event) {
            var target = $(event.currentTarget).closest(this.visibleOptionSelector)[0];

            if (this.isCursorPositionChange(event) || this.hoveredElement === target) {
                return;
            }

            this._hoverTo(target);
            this.setCursorPosition(event);
        },

        /**
         * Get option index
         *
         * @param {Object} data - object with data about this element
         *
         * @returns {Number}
         */
        getOptionIndex: function (data) {
            var index;

            _.each(this.cacheOptions.plain, function (opt, id) {
                if (data.value === opt.value) {
                    index = id;
                }
            });

            return index;
        },

        /**
         * Set X and Y cursor position
         *
         * @param {Object} event - mousemove event
         */
        setCursorPosition: function (event) {
            this.cursorPosition = {
                x: event.pageX,
                y: event.pageY
            };
        },

        /**
         * Check previous and current cursor position
         *
         * @param {Object} event - mousemove event
         * @returns {Boolean}
         */
        isCursorPositionChange: function (event) {
            return this.cursorPosition &&
                this.cursorPosition.x === event.pageX &&
                this.cursorPosition.y === event.pageY;
        },

        /**
         * Set true to observable variable multiselectFocus
         * @param {Object} ctx
         * @param {Object} event - focus event
         */
        onFocusIn: function (ctx, event) {
            !this.cacheUiSelect ? this.cacheUiSelect = event.target : false;
            this.multiselectFocus(true);
        },

        /**
         * Set false to observable variable multiselectFocus
         * and close list
         */
        onFocusOut: function () {
            this.multiselectFocus(false);
        },

        /**
         * Handler enter key, if select list is closed - open select,
         * if select list is open toggle selected current option
         */
        enterKeyHandler: function () {

            if (this.filterOptionsFocus()) {
                return false;
            }

            if (this.listVisible()) {
                if (this.hoveredElement) {
                    this.toggleOptionSelected(ko.dataFor(this.hoveredElement));
                }
            } else {
                this.setListVisible(true);
            }
        },

        /**
         * Handler escape key, if select list is open - closes it,
         */
        escapeKeyHandler: function () {
            this.listVisible() ? this.setListVisible(false) : false;
        },

        /**
         * Handler pageDown key, selected next option in list, if current option is last
         * selected first option in list
         */
        pageDownKeyHandler: function () {
            this._setHoverToElement(1);
        },

        /**
         * Get jQuery element by option data
         *
         * @param {Object} data - option data
         *
         * @returns {Object} jQuery element
         */
        _getElemByData: function (data) {
            var i = 0,
                list = $(this.cacheUiSelect).find('li'),
                length = this.options().length,
                result;

            for (i; i < length; i++) {
                if (this.options()[i].value === data.value) {
                    result = $(list[i]);
                }
            }

            return result;
        },

        /**
         * Set hover to visible element
         *
         * @param {Number} direction - iterator
         */
        _setHoverToElement: function (direction) {
            var element;

            if (direction ===  1) {
                element = this._getNextElement();
            } else if (direction === -1) {
                element = this._getPreviousElement();
            }

            if (element) {
                this._hoverTo(element);
                this._scrollTo(element);
            }
        },

        /**
         * Find current hovered element
         * and change scroll position
         *
         * @param {Number} index - element index
         */
        _scrollTo: function (element) {
            var curEl = $(element).children(this.actionTargetSelector),
                wrapper = $(this.rootList),
                curElPos = {},
                wrapperPos = {};

            curElPos.start = curEl.offset().top;
            curElPos.end = curElPos.start + curEl.outerHeight();

            wrapperPos.start = wrapper.offset().top;
            wrapperPos.end = wrapperPos.start + wrapper.height();

            if (curElPos.start < wrapperPos.start) {
                wrapper.scrollTop(wrapper.scrollTop() - (wrapperPos.start - curElPos.start));
            } else if (curElPos.end > wrapperPos.end) {
                wrapper.scrollTop(wrapper.scrollTop() + curElPos.end - wrapperPos.end);
            }
        },

        /**
         * Handler pageUp key, selected previous option in list, if current option is first -
         * selected last option in list
         */
        pageUpKeyHandler: function () {
            this._setHoverToElement(-1);
        },

        /**
         * Switcher to parse keydown event and delegate event to needful method
         *
         * @param {Object} data - element data
         * @param {Object} event - keydown event
         * @returns {Boolean} if handler for this event doesn't found return true
         */
        keydownSwitcher: function (data, event) {
            var keyName = keyCodes[event.keyCode];

            if (this.isTabKey(event)) {
                if (!this.filterOptionsFocus() && this.listVisible() && this.filterOptions) {
                    this.cacheUiSelect.blur();
                    this.filterOptionsFocus(true);
                    this.cleanHoveredElement();

                    return false;
                }
                this.listVisible(false);

                return true;
            }

            if (this.keyDownHandlers().hasOwnProperty(keyName)) {
                this.keyDownHandlers()[keyName].apply(this, arguments);
            } else {
                return true;
            }
        },

        /**
         * Set caption
         */
        setCaption: function () {
            var length;

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length && this.getSelected().length) {
                this.placeholder(this.getSelected()[0].label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        },

        /**
         * Set list status, open or close
         *
         * @param {Boolean} value - variable for set list visible status
         */
        setListVisible: function (value) {
            this.listVisible(value);
        },

        /**
         * Processes preview for option by it's value, and sets the result
         * to 'preview' observable
         *
         * @returns {String}
         */
        getPreview: function () {
            var selected = this.getSelected();

            return selected.map(function (option) {
                return option.label;
            }).join(', ');
        },

        /**
         * Defines previous option element to
         * the one that is currently hovered.
         *
         * @returns {Element}
         */
        _getPreviousElement: function () {
            var currentElement = this.hoveredElement,
                lastElement    = this._getLastIn(this.rootList),
                previousElement;

            if (!currentElement) {
                return lastElement;
            }

            previousElement = $(currentElement).prev()[0];

            return (
                this._getLastIn(previousElement) ||
                previousElement ||
                this._getFirstParentOf(currentElement) ||
                lastElement
            );
        },

        /**
         * Defines next option element to
         * the one that is currently hovered.
         *
         * @returns {Element}
         */
        _getNextElement: function () {
            var currentElement = this.hoveredElement,
                firstElement   = this._getFirstIn(this.rootList);

            if (!currentElement) {
                return firstElement;
            }

            return (
                this._getFirstIn(currentElement) ||
                $(currentElement).next()[0] ||
                this._getParentsOf(currentElement).next()[0] ||
                firstElement
            );
        },

        /**
         * Returns first option element in provided scope.
         *
         * @param {Element} scope
         * @returns {Element}
         */
        _getFirstIn: function (scope) {
            return $(scope).find(this.visibleOptionSelector)[0];
        },

        /**
         * Returns last descendant option element in provided scope.
         *
         * @param {Element} scope
         * @returns {Element}
         */
        _getLastIn: function (scope) {
            return $(scope).find(this.visibleOptionSelector).last()[0];
        },

        /**
         * Returns a collection of parent option elements.
         *
         * @param {Element} scope
         * @returns {jQueryCollection}
         */
        _getParentsOf: function (scope) {
            return $(scope).parents(this.visibleOptionSelector);
        },

        /**
         * Returns first parent option element.
         *
         * @param {Element} scope
         * @returns {Element}
         */
        _getFirstParentOf: function (scope) {
            return this._getParentsOf(scope)[0];
        },

        /**
         * Sets hover class to provided option element.
         *
         * @param {Element} element
         */
        _hoverTo: function(element) {
            if (this.hoveredElement) {
                $(this.hoveredElement)
                    .children(this.actionTargetSelector)
                    .removeClass(this.hoverClass);
            }

            $(element)
                .children(this.actionTargetSelector)
                .addClass(this.hoverClass);

            this.hoveredElement = element;
        },

        /**
         * Callback which fires when root list element is rendered.
         *
         * @param {Element} element
         */
        onRootListRender: function (element) {
            var targetSelector = 'li > ' + this.actionTargetSelector;

            this.rootList = element;

            $(this.rootList).on(
                'mousemove',
                targetSelector,
                this.onDelegatedMouseMouve.bind(this)
            );
        }
    });
});
