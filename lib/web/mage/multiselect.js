/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'text!mage/multiselect.html',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'jquery/editableMultiselect/js/jquery.multiselect'
], function (_, $, searchTemplate, alert) {
    'use strict';

    var nativeConstructor = $.fn.multiselect;

    $.widget('mage.multiselect2', {
        options: {
            mselectContainer: 'section.mselect-list',
            mselectItemsWrapperClass: 'mselect-items-wrapper',
            mselectCheckedClass: 'mselect-checked',
            containerClass: 'paginated',
            searchInputClass: 'admin__action-multiselect-search',
            selectedItemsCountClass: 'admin__action-multiselect-items-selected',
            currentPage: 1,
            lastAppendValue: 0,
            updateDelay: 500,
            optionsLoaded: false
        },

        /** @inheritdoc */
        _create: function () {
            nativeConstructor.call(this.element, this.options);
        },

        /** @inheritdoc */
        _init: function () {
            this.domElement = this.element.get(0);

            this.$container = $(this.options.mselectContainer);
            this.$wrapper = this.$container.find('.' + this.options.mselectItemsWrapperClass);
            this.$item = this.$wrapper.find('div').first();
            this.selectedValues = [];

            this.$container.addClass(this.options.containerClass).prepend(searchTemplate);
            this.$input = this.$container.find('.' + this.options.searchInputClass);
            this.$selectedCounter = this.$container.find('.' + this.options.selectedItemsCountClass);
            this.filter = '';

            if (this.domElement.options.length) {
                this._setLastAppendOption(this.domElement.options[this.domElement.options.length - 1].value);
            }

            this._initElement();
            this._events();
        },

        /**
         * Leave only saved/selected options in select element.
         *
         * @private
         */
        _initElement: function () {
            this.element.empty();
            _.each(this.options.selectedValues, function (value) {
                this._createSelectedOption({
                    value: value,
                    label: value
                });
            }, this);
        },

        /**
         * Attach required events.
         *
         * @private
         */
        _events: function () {
            var onKeyUp = _.debounce(this.onKeyUp, this.options.updateDelay);

            _.bindAll(this, 'onScroll', 'onCheck', 'onOptionsChange');

            this.$wrapper.on('scroll', this.onScroll);
            this.$wrapper.on('change.mselectCheck', '[type=checkbox]', this.onCheck);
            this.$input.on('keyup', _.bind(onKeyUp, this));
            this.element.on('change.hiddenSelect', this.onOptionsChange);
        },

        /**
         * Behaves multiselect scroll.
         */
        onScroll: function () {
            var height = this.$wrapper.height(),
                scrollHeight = this.$wrapper.prop('scrollHeight'),
                scrollTop = Math.ceil(this.$wrapper.prop('scrollTop'));

            if (!this.options.optionsLoaded && scrollHeight - height <= scrollTop) {
                this.loadOptions();
            }
        },

        /**
         * Behaves keyup event on input search
         */
        onKeyUp: function () {
            if (this.getSearchCriteria() === this.filter) {
                return false;
            }

            this.setFilter();
            this.emptyMultiselectOptions();
            this.loadOptions(1);
        },

        /**
         * Callback for select change event
         */
        onOptionsChange: function () {
            this.selectedValues = _.map(this.domElement.options, function (option) {
                return option.value;
            }, this);

            this._updateSelectedCounter();
        },

        /**
         * Overrides native check behaviour.
         *
         * @param {Event} event
         */
        onCheck: function (event) {
            var checkbox = event.target,
                option = {
                    value: checkbox.value,
                    label: $(checkbox.labels[0]).text()
                };

            checkbox.checked ? this._createSelectedOption(option) : this._removeSelectedOption(option);
            event.stopPropagation();
        },

        /**
         * Show error message.
         *
         * @param {String} message
         */
        onError: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Updates current filter state.
         */
        setFilter: function () {
            this.filter = this.getSearchCriteria() || '';
        },

        /**
         * Reads search input value.
         *
         * @return {String}
         */
        getSearchCriteria: function () {
            return $.trim(this.$input.val());
        },

        /**
         * Load options data.
         */
        loadOptions: function (page) {
            var self = this,
                requestPage = page || ++this.options.currentPage;

            this.$wrapper.trigger('processStart');
            this.$input.prop('disabled', true);

            $.get(this.options.nextPageUrl, {
                p: requestPage,
                s: this.filter
            }).done(function (response) {
                if (response.success) {
                    self.appendOptions(response.result);
                } else {
                    self.onError(response.errorMessage);
                }
            }).always(function () {
                self.$wrapper.trigger('processStop');
                self.$input.prop('disabled', false);

                if (self.filter) {
                    self.$input.focus();
                }
            });
        },

        /**
         * Append loaded options
         *
         * @param {Array} options
         */
        appendOptions: function (options) {
            var divOptions = [];

            if (!options.length) {
                return false;
            }

            if (this.isOptionsLoaded(options)) {
                return;
            }

            options.forEach(function (option) {
                option.selected = this._isOptionSelected(option);
                divOptions.push(this._createMultiSelectOption(option));
                this._setLastAppendOption(option.value);
            }, this);

            this.$wrapper.append(divOptions);
        },

        /**
         * Clear multiselect options
         */
        emptyMultiselectOptions: function () {
            this._setLastAppendOption(0);
            this.$wrapper.empty();
        },

        /**
         * Checks if all options are already loaded
         *
         * @return {Boolean}
         */
        isOptionsLoaded: function (options) {
            this.options.optionsLoaded = this.options.lastAppendValue === options[options.length - 1].value;

            return this.options.optionsLoaded;
        },

        /**
         * Creates new selected option for select element
         *
         * @param {Object} option - option object
         * @param {String} option.value - option value
         * @param {String} option.label - option label
         * @private
         */
        _createSelectedOption: function (option) {
            var selectOption = new Option(option.value, option.value, false, true);

            this.element.append(selectOption);
            this.selectedValues.push(option.value);
            this._updateSelectedCounter();

            return selectOption;
        },

        /**
         * Remove passed option from select element
         *
         * @param {Object} option - option object
         * @param {String} option.value - option value
         * @param {String} option.label - option label
         * @return {Object} option
         * @private
         */
        _removeSelectedOption: function (option) {
            var unselectedOption = _.findWhere(this.domElement.options, {
                value: option.value
            });

            if (!_.isUndefined(unselectedOption)) {
                this.domElement.remove(unselectedOption.index);
                this.selectedValues.splice(_.indexOf(this.selectedValues, option.value), 1);
                this._updateSelectedCounter();
            }

            return unselectedOption;
        },

        /**
         * Creates new DIV option for multiselect widget
         *
         * @param {Object} option - option object
         * @param {String} option.value - option value
         * @param {String} option.label - option label
         * @param {Boolean} option.selected - is option selected
         * @private
         */
        _createMultiSelectOption: function (option) {
            var item = this.$item.clone(),
                checkbox = item.find('input'),
                isSelected = !!option.selected;

            checkbox.val(option.value)
                .prop('checked', isSelected)
                .toggleClass(this.options.mselectCheckedClass, isSelected);

            item.find('label > span').text(option.label);

            return item;
        },

        /**
         * Checks if passed option should be selected
         *
         * @param {Object} option - option object
         * @param {String} option.value - option value
         * @param {String} option.label - option label
         * @param {Boolean} option.selected - is option selected
         * @return {Boolean}
         * @private
         */
        _isOptionSelected: function (option) {
            return !!~this.selectedValues.indexOf(option.value);
        },

        /**
         * Saves last added option value.
         *
         * @param {Number} value
         * @private
         */
        _setLastAppendOption: function (value) {
            this.options.lastAppendValue = value;
        },

        /**
         * Updates counter of selected items.
         *
         * @private
         */
        _updateSelectedCounter: function () {
            this.$selectedCounter.text(this.selectedValues.length);
        }
    });

    return $.mage.multiselect2;
});
