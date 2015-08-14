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

    return Abstract.extend({
        defaults: {
            options: [],
            listVisible: false,
            multiselectFocus: false,
            selected: [],
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
                listVisible: 'cleanHoveredElement'
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
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe(['listVisible', 'selected', 'hoverElIndex', 'placeholder', 'multiselectFocus', 'options']);

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
         * Clean hoverElIndex variable
         *
         * @returns {Object} Chainable
         */
        cleanHoveredElement: function () {
            if (!this.listVisible() && !_.isNull(this.hoverElIndex())) {
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
        onFocusIn: function () {
            this.multiselectFocus(true);
        },

        /**
         * Set false to observable variable multiselectFocus
         * and close list
         */
        onFocusOut: function () {
            this.multiselectFocus(false);
            this.listVisible() ? this.listVisible(false) : false;
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
            var handlers = {
                    enterKey: this.enterKeyHandler,
                    escapeKey: this.escapeKeyHandler,
                    spaceKey: this.enterKeyHandler,
                    pageUpKey: this.pageUpKeyHandler,
                    pageDownKey: this.pageDownKeyHandler
                },
                keyName = keyCodes[event.keyCode];

            if (handlers.hasOwnProperty(keyName)) {
                handlers[keyName].apply(this, arguments);
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
            this.value(this.selected());
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
