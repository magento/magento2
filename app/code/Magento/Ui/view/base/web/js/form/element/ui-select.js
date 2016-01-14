/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './abstract',
    'Magento_Ui/js/lib/key-codes',
    'mage/translate',
    'ko',
    'jquery'
], function (_, Abstract, keyCodes, $t, ko, $) {
    'use strict';

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
            length = array.length,
            childCollection;

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
     *
     * @returns {Array} Array with levels
     */
    function setLevelsProperty(array, separator, level) {
        var i = 0,
            length = array.length;

        level = level || 0;

        for (i; i < length; i++) {
            array[i].level = level;

            if (array[i].hasOwnProperty(separator)) {
                level++;
                setLevelsProperty.call(this, array[i][separator], separator, level);
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

        nodes = setLevelsProperty(nodes, 'optgroup');
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
            filterInputValue: '',
            filterOptionsFocus: false,
            multiselectFocus: false,
            simpleMode: false,
            optgroupMode: false,
            lastSelectable: false,
            showCheckbox: true,
            levelsVisibility: true,
            openLevelsAction: true,
            showOpenLevelsActionIcon: true,
            optgroupLabels: false,
            closeBtn: true,
            showTree: false,
            labelsDecoration: false,
            closeBtnLabel: $t('Done'),
            optgroupTmpl: 'ui/grid/filters/elements/ui-select-optgroup',
            selectedPlaceholders: {
                defaultPlaceholder: $t('Select...'),
                lotPlaceholders: $t('Selected')
            },
            hoverElIndex: null,
            separator: 'optgroup',
            listens: {
                listVisible: 'cleanHoveredElement',
                filterInputValue: 'filterOptionsList'
            }
        },

        /**
         * Initialize conponent
         */
        initialize: function () {
            this._super()
                .preprocessingConfig();

            return this;
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            var result = parseOptions(config.options);

            _.extend(config, result);

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
            return this.hasChildList() && !this.optgroupMode;
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
            this.observe(['listVisible',
                'hoverElIndex',
                'placeholder',
                'multiselectFocus',
                'options',
                'filterInputValue',
                'filterOptionsFocus'
            ]);

            return this;
        },

        /**
         * Change configuration for different mods
         */
        preprocessingConfig: function () {
            if (this.simpleMode) {
                this.showCheckbox = false;
                this.chipsEnabled = false;
                this.closeBtn = false;
            }

            if (this.optgroupMode) {
                this.showCheckbox = false;
                this.lastSelectable = true;
                this.optgroupLabels = true;
                this.openLevelsAction = false;
                this.labelsDecoration = true;
            }

            if (this.lastSelectable) {
                this.levelsVisibility = true;
            }

            if (!this.openLevelsAction) {
                this.showOpenLevelsActionIcon = false;
            }

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
            var curLevel = ++data.level;

            if (!data.visible) {
                data.visible = ko.observable(!!data.hasOwnProperty(this.separator) &&
                    _.isBoolean(this.levelsVisibility) &&
                    this.levelsVisibility ||
                    data.hasOwnProperty(this.separator) && parseInt(this.levelsVisibility, 10) >= curLevel);

            }

            return data.visible();
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
            var i = 0,
                array = [],
                curOption,
                value = this.filterInputValue().trim().toLowerCase();

            if (value === '') {
                this.options(this.cacheOptions.tree);

                return false;
            }

            this.options(this.cacheOptions.plain);

            if (this.filterInputValue()) {
                for (i; i < this.options().length; i++) {
                    curOption = this.options()[i].label.toLowerCase();

                    if (curOption.indexOf(value) > -1) {
                        array.push(this.options()[i]); /*eslint max-depth: [2, 4]*/
                    }
                }

                if (!value.length) {
                    this.options(this.cacheOptions.plain);
                } else {
                    this.options(array);
                }
                this.cleanHoveredElement();
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
            if (!_.isNull(this.hoverElIndex())) {
                this.hoverElIndex(null);
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
            return _.contains(this.value(), value);
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
         * @param {String} elem - element
         * @return {Boolean}
         */
        isHovered: function (data, elem) {
            var index = this.getOptionIndex(data),
                status = this.hoverElIndex() === index;

            if (this.optgroupMode && data.hasOwnProperty(this.separator)) {
                return false;
            }

            if (
                status &&
                elem.offsetTop > elem.parentNode.offsetHeight ||
                status &&
                elem.parentNode.scrollTop > elem.offsetTop - elem.parentNode.offsetTop
            ) {
                elem.parentNode.scrollTop = elem.offsetTop - elem.parentNode.offsetTop;
            }

            return status;
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

            if (this.simpleMode && !isSelected) {
                this.value(data.value);
                this.listVisible(false);
            } else if (!isSelected) {
                this.value.push(data.value);
            } else {
                this.value(_.without(this.value(), data.value));
            }

            return this;
        },

        /**
         * Change visibility to child level
         *
         * @param {Object} data - element data
         * @param {Object} elem - element
         */
        openChildLevel: function (data, elem) {
            var contextElement;

            if (
                this.openLevelsAction &&
                data.hasOwnProperty(this.separator) && _.isBoolean(this.levelsVisibility) ||
                this.openLevelsAction &&
                data.hasOwnProperty(this.separator) && parseInt(this.levelsVisibility, 10) <= data.level
            ) {
                contextElement = ko.contextFor($(elem).parents('li').children('ul')[0]).$data.current;
                contextElement.visible(!contextElement.visible());
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
         * Add hover to some list element and clears element ID to variable
         *
         * @param {Object} data - object with data about this element
         * @param {Number} index - element index
         * @param {Object} event - mousemove event
         */
        onMousemove: function (data, index, event) {
            var id,
                context = ko.contextFor(event.target);

            if (this.isCursorPositionChange(event)) {
                return false;
            }

            if (typeof context.$data === 'object') {
                id = this.getOptionIndex(context.$data);
            }

            id !== this.hoverElIndex() ? this.hoverElIndex(id) : false;

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

            if (this.listVisible()) {
                if (!_.isNull(this.hoverElIndex())) {
                    this.toggleOptionSelected(this.options()[this.hoverElIndex()]);
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
            if (!_.isNull(this.hoverElIndex())) {
                if (this.hoverElIndex() !== this.options().length - 1) {
                    this.hoverElIndex(this.hoverElIndex() + 1);
                } else {
                    this.hoverElIndex(0);
                }
            } else {
                this.hoverElIndex(0);
            }
        },

        /**
         * Handler pageUp key, selected previous option in list, if current option is first -
         * selected last option in list
         */
        pageUpKeyHandler: function () {
            if (!_.isNull(this.hoverElIndex())) {
                if (this.hoverElIndex() !== 0) {
                    this.hoverElIndex(this.hoverElIndex() - 1);
                } else {
                    this.hoverElIndex(this.options().length - 1);
                }
            } else {
                this.hoverElIndex(this.options().length - 1);
            }
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

            if (!_.isArray(this.value())) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length) {
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
        }
    });
});
