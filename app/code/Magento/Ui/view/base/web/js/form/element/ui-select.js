/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'ko',
    './abstract',
    'Magento_Ui/js/lib/key-codes',
    'mage/translate',
    'uiLayout'
], function (_, ko, Abstract, keyCodes, $t, layout) {
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
            multiselectFocus: false,
            selected: [],
            filterOptions: false,
            chipsEnabled: true,
            cacheUiSelect: null,
            filterInputValue: '',
            filterOptionsFocus: false,
            selectedPlaceholders: {
                defaultPlaceholder: $t('Select...'),
                lotPlaceholders: $t('Selected')
            },
            optionsConfig: {
                name: '${ $.name }_options',
                component: 'Magento_Ui/js/form/element/helpers/options'
            },
            hoverElIndex: null,
            listens: {
                selected: 'setCaption setValue',
                listVisible: 'cleanHoveredElement',
                filterInputValue: 'filterOptionsList'
            },
            imports: {
                options: '${ $.optionsConfig.name }:options'
            },
            modules: {
                optionsProvider: '${ $.optionsConfig.name }'
            }
        },

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *
         * @returns {Object} Chainable
         */
        initialize: function () {
            this._super()
                .initOptions();

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
                          'selected',
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
         * Initializes optionsProvider
         *
         * @returns {Object} Chainable.
         */
        initOptions: function () {
            this.optionsConfig.options = this.options();
            layout([this.optionsConfig]);

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
         * Checked has selected elements or not
         *
         * @returns {Boolean}
         */
        hasSelected: function () {
            return !!this.selected().length;
        },

        /**
         * Remove element from selected array
         */
        removeSelected: function (data, event) {
            event ? event.stopPropagation() : false;
            this.selected(_.without(this.selected(), data));
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
            return _.contains(this.selected(), label);
        },

        /**
         * Check hovered option
         *
         * @param {String} index - element index
         * @return {Boolean}
         */
        isHovered: function (index, elem) {
            var status = this.hoverElIndex() === index;

            if (status &&
                elem.offsetTop > elem.parentNode.offsetTop + elem.parentNode.offsetHeight ||
                status &&
                elem.parentNode.scrollTop > elem.offsetTop
            ) {
                elem.parentNode.scrollTop = elem.offsetTop;
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
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            if (!_.contains(this.selected(), data.label)) {
                this.selected.push(data.label);
            } else {
                this.selected(_.without(this.selected(), data.label));
            }

            return this;
        },

        /**
         * Add hover to some list element and clears element ID to variable
         *
         * @param {Object} data - object with data about this element
         * @param {Number} index - element index
         */
        onHoveredIn: function (data, index) {
            this.hoverElIndex(index);
        },

        /**
         * Remove hover to some list element and write element ID from variable
         */
        onHoveredOut: function () {
            this.hoverElIndex(null);
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
                if (!this.filterOptionsFocus() && this.listVisible()) {
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
            var length = this.selected().length;

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length) {
                this.placeholder(this.selected()[0]);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        },

        /**
         * Preprocessing array values to string and set to value variable
         */
        setValue: function () {
            var selected = this.selected(),
                length = selected.length,
                i = 0,
                array = [];

            for (i; i < length; i++) {
                if (this.cacheOptions[i].label === selected[i])
                array.push(this.cacheOptions[i].value);
            }

            this.value(array);
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
            return this.selected().toString();
        }
    });
});
