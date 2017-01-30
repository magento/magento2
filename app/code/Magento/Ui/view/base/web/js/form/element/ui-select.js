/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './abstract',
    'Magento_Ui/js/lib/key-codes',
    'mage/translate',
    'jquery'
], function (_, Abstract, keyCodes, $t, $) {
    'use strict';

    /**
     * Preprocessing options list
     */
    function parseOptions(nodes) {
        var caption,
            value;

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
            cacheOptions: _.compact(nodes)
        };
    }

    return Abstract.extend({
        defaults: {
            options: [],
            listVisible: false,
            value: [],
            filterOptions: false,
            chipsEnabled: false,
            filterInputValue: '',
            filterOptionsFocus: false,
            multiselectFocus: false,
            selectedPlaceholders: {
                defaultPlaceholder: $t('Select...'),
                lotPlaceholders: $t('Selected')
            },
            hoverElIndex: null,
            listens: {
                listVisible: 'cleanHoveredElement',
                filterInputValue: 'filterOptionsList'
            }
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
                value;

            this.options(this.cacheOptions);

            if (this.filterInputValue()) {
                for (i; i < this.options().length; i++) {
                    curOption = this.options()[i].label.toLowerCase();
                    value = this.filterInputValue().trim().toLowerCase();

                    if (curOption.indexOf(value) > -1) {
                        array.push(this.options()[i]);
                    }
                }

                if (!value.length) {
                    this.options(this.cacheOptions);
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
         * @param {String} label - option label
         * @return {Boolean}
         */
        isSelected: function (label) {
            return _.contains(this.value(), label);
        },

        /**
         * Check hovered option
         *
         * @param {String} index - element index
         * @return {Boolean}
         */
        isHovered: function (index, elem) {
            var status = this.hoverElIndex() === index;

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
         * Get filtered value*
         */
        getValue: function () {
            var options = this.options(),
                selected = this.value();

            _.chain(options)
                .pluck(options, 'value')
                .filter(function (opt) {
                    return _.contains(selected, opt);
                });
        },

        /**
         * Get selected element labels
         *
         * @returns {Array} array labels
         */
        getSelected: function () {
            var selected = this.value();

            return this.cacheOptions.filter(function (opt) {
                return _.contains(selected, opt.value);
            });
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            if (!_.contains(this.value(), data.value)) {
                this.value.push(data.value);
            } else {
                this.value(_.without(this.value(), data.value));
            }

            return this;
        },

        /**
         * Check selected elements
         *
         * @returns {Bollean}
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
            var target = $(event.target),
                id;

            if (this.isCursorPositionChange(event)) {
                return false;
            }

            target.is('li') ? id = target.index() : id = target.parent('li').index();
            id !== this.hoverElIndex() ? this.hoverElIndex(id) : false;

            this.setCursorPosition(event);
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
         */
        onFocusIn: function (elem) {
            !this.cacheUiSelect ? this.cacheUiSelect = elem : false;
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

            if (this.value()) {
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
