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
            caption: 'Select...',
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
            selectedCounter: 0,
            hoveredElementIndex: null,
            selectedVariable: [],
            selected: [],
            selectedPlaceholders: {
                defaultPlaceholder: 'Select...',
                lotPlaceholders: ' Selected'
            },

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
            var i = 0,
                length = this.options.length,
                curOption;

            this._super();
            this.observe('multiselectFocus');
            this.observe('caption');
            this.observe(['listVisible', 'selected']);

            for (i; i < length; i++) {
                curOption = this.options[i];
                curOption.selected = ko.observable(false);
                curOption.hovered = ko.observable(false);
            }

            return this;
        },

        /**
         * preprocessing array values to string and set to value variable
         */
        setValue: function () {
            this.value(this.selected());
        },

        /**
         * clean hover from element and clean hoveredElementIndex variable
         */
        cleanHoveredElement: function () {
            if (!this.listVisible() && !_.isNull(this.hoveredElementIndex)) {
                this.onHoveredOut(this.options[this.hoveredElementIndex]);
                this.hoveredElementIndex = null;
            }

            return this;
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
            data.selected(!data.selected());

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
            this.hoveredElementIndex = index;
            data.hovered(true);
        },

        /**
         * onHoveredOut: Remove hover to some list element and write element ID from variable
         * @param {Object} data - object with data about this element
         */
        onHoveredOut: function (data) {
            data.hovered(false);
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
                !_.isNull(this.hoveredElementIndex) ?
                    this.proxyOptionsClick(this.options[this.hoveredElementIndex]) : false;
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
            if (!_.isNull(this.hoveredElementIndex)) {
                this.onHoveredOut(this.options[this.hoveredElementIndex]);
                this.hoveredElementIndex !== this.options.length - 1 ?
                      this.hoveredElementIndex++
                    : this.onHoveredIn(this.options[0], 0);
                this.onHoveredIn(this.options[this.hoveredElementIndex], this.hoveredElementIndex);
            } else {
                this.onHoveredIn(this.options[0], 0);
            }
        },

        /**
         * pageUpKeyHandler: handler pageUp key, selected previous option in list, if current option is first -
         * selected last option in list
         */
        pageUpKeyHandler: function () {
            if (!_.isNull(this.hoveredElementIndex)) {
                this.onHoveredOut(this.options[this.hoveredElementIndex]);
                this.hoveredElementIndex !== 0 ?
                      this.hoveredElementIndex--
                    : this.onHoveredIn(this.options[this.options.length - 1], this.options.length - 1);
                this.onHoveredIn(this.options[this.hoveredElementIndex], this.hoveredElementIndex);
            } else {
                this.onHoveredIn(this.options[this.options.length - 1], this.options.length - 1);
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
                this.caption(length + this.selectedPlaceholders.lotPlaceholders);
            } else if (length) {
                this.caption(this.selected()[0].label);
            } else {
                this.caption(this.selectedPlaceholders.defaultPlaceholder);
            }
        },

        /**
         * setToSelectedArray: set data item to array selected elements
         */
        setToSelectedArray: function () {
            this.selected(_.compact(this.selectedVariable));
        },

        /**
         * setListVisible: set list status, open or close
         */
        setListVisible: function (value) {
            this.listVisible(value);
        },

        /**
         * setToSelectedVariableArray: set or remove data to variable array,
         * this array can has empty values because data sets and removes to
         * array by self index
         */
        setToSelectedVariableArray: function (data, index) {
            data.selected() ? this.selectedVariable[index] = data : this.selectedVariable[index] = null;
        },

        /**
         * proxyOptionsClick: proxy function for delegation data to support methods
         */
        proxyOptionsClick: function (data, index) {
            this.toggleOptionSelected(data);
            this.setToSelectedVariableArray(data, index);
            this.setToSelectedArray();
        },

        /**
         * Processes preview for option by it's value, and sets the result
         * to 'preview' observable
         *
         * @param {String}
         * @returns {String}
         */
        getPreview: function () {
            var i = 0,
                selectedArray = this.selected(),
                length = selectedArray.length,
                value = '';

            for (i; i < length; i++) {
                i > 0 ? value += ', ' + selectedArray[i].label : value += selectedArray[i].label;
            }

            return value;
        }
    });
});