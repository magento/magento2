/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'ko',
    './abstract',
    'Magento_Ui/js/lib/keyCodes'
], function (_, ko, Abstract, keyCodes) {
    'use strict';

    return Abstract.extend({
        defaults: {
            options: [
                {
                    label: '1 column',
                    value: '1column'
                }, {
                    label: '2 column',
                    value: '2column'
                }, {
                    label: '3 column',
                    value: '3column'
                }
            ],
            listVisible: false,
            multiselectFocus: false,
            selected: [],
            selectedPlaceholders: {
                defaultPlaceholder: 'Select...',
                lotPlaceholders: ' Selected'
            },
            hoverElIndex: null,

            listens: {
                selected: 'setCaption setValue',
                listVisible: 'cleanHoveredElement'
            }
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe(['listVisible', 'selected', 'hoverElIndex', 'placeholder', 'multiselectFocus']);

            return this;
        },

        /**
         * clean hoverElIndex variable
         */
        cleanHoveredElement: function () {
            if (!this.listVisible() && !_.isNull(this.hoverElIndex())) {
                this.hoverElIndex(null);
            }

            return this;
        },

        /**
         *  "IS" METHODS
         *
         * check selected option
         */
        isSelected: function (label) {
            return _.contains(this.selected(), label);
        },

        /**
         *  "IS" METHODS
         *
         * check hovered option
         */
        isHovered: function (index) {
            return this.hoverElIndex() === index;
        },

        /**
         *   "TOGGLE" METHODS
         *
         * Toggle list visibility
         * @returns {Object} this context.
         */
        toggleListVisible: function () {
            this.listVisible(!this.listVisible());

            return this;
        },

        /**
         * Toggle activity list element
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
         *   "ON" METHODS
         *
         * onHoveredIn: Add hover to some list element and clears element ID to variable
         * @param {Object} data - object with data about this element
         * @param {Number} index - element index
         */
        onHoveredIn: function (data, index) {
            this.hoverElIndex(index);
        },

        /**
         * onHoveredOut: Remove hover to some list element and write element ID from variable
         * @param {Object} data - object with data about this element
         */
        onHoveredOut: function (data, index) {
            this.hoverElIndex(index);
        },

        /**
         * onFocusIn: Set true to observable variable multiselectFocus
         */
        onFocusIn: function () {
            this.multiselectFocus(true);
        },

        /**
         * onFocusOut: Set false to observable variable multiselectFocus
         * and close list
         */
        onFocusOut: function () {
            this.multiselectFocus(false);
            this.listVisible() ? this.listVisible(false) : false;
        },

        /**
         *  KEYDOWN HANDLERS
         *
         * enterKeyHandler: handler enter key, if select list is closed - open select,
         * if select list is open toggle selected current option
         */
        enterKeyHandler: function () {
            if (this.listVisible()) {
                if (!_.isNull(this.hoverElIndex())) {
                    this.toggleOptionSelected(this.options[this.hoverElIndex()]);
                }
            } else {
                this.setListVisible(true);
            }
        },

        /**
         * escapeKeyHandler: handler escape key, if select list is open - closes it,
         */
        escapeKeyHandler: function () {
            this.listVisible() ? this.setListVisible(false) : false;
        },

        /**
         * pageDownKeyHandler: handler pageDown key, selected next option in list, if current option is last
         * selected first option in list
         */
        pageDownKeyHandler: function () {
            if (!_.isNull(this.hoverElIndex())) {
                if (this.hoverElIndex() !== this.options.length - 1) {
                    this.hoverElIndex(this.hoverElIndex() + 1);
                } else {
                    this.hoverElIndex(0);
                }
            } else {
                this.hoverElIndex(0);
            }
        },

        /**
         * pageUpKeyHandler: handler pageUp key, selected previous option in list, if current option is first -
         * selected last option in list
         */
        pageUpKeyHandler: function () {
            if (!_.isNull(this.hoverElIndex())) {
                if (this.hoverElIndex() !== 0) {
                    this.hoverElIndex(this.hoverElIndex() - 1);
                } else {
                    this.hoverElIndex(this.options.length - 1);
                }
            } else {
                this.hoverElIndex(this.options.length - 1);
            }
        },

        /**
         * keydownSwitcher: switcher to parse keydown event and delegate event to needful method
         */
        keydownSwitcher: function (data, event) {
            var handlers = {
                    'enterKey': this.enterKeyHandler,
                    'escapeKey': this.escapeKeyHandler,
                    'spaceKey': this.enterKeyHandler,
                    'pageUpKey': this.pageUpKeyHandler,
                    'pageDownKey': this.pageDownKeyHandler
                },
                keyName = keyCodes[event.keyCode];

            if (handlers.hasOwnProperty(keyName)) {
                handlers[keyName].apply(this, arguments);
            } else {

                return true;
            }
        },

        /**
         * setCaption: set caption
         */
        setCaption: function () {
            var length = this.selected().length;

            if (length && length !== 1) {
                this.placeholder(length + this.selectedPlaceholders.lotPlaceholders);
            } else if (length) {
                this.placeholder(this.selected()[0]);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        },

        /**
         * preprocessing array values to string and set to value variable
         */
        setValue: function () {
            this.value(this.selected());
        },

        /**
         * setListVisible: set list status, open or close
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