/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, layout, Collapsible) {
    'use strict';

    /*eslint-disable no-extra-parens*/
    /**
     * Defines if element is visible inside of a specified container.
     *
     * @param {HTMLElement} elem - Element that should be checked.
     * @param {HTMLElement} container - Container inside of which element is located.
     * @returns {Boolean}
     */
    function isInVieport(elem, container) {
        var containerHeight = container.clientHeight,
            offset;

        if (container.scrollHeight <= containerHeight) {
            return true;
        }

        offset = elem.offsetTop - container.scrollTop;

        return (
            offset >= -1 &&
            offset + elem.clientHeight <= containerHeight
        );
    }
    /*eslint-enable no-extra-parens*/

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/search/options',
            valueKey: 'value',
            labelKey: 'label',
            navigated: -1,
            options: [],
            providerConfig: {
                provider: '${ $.providerConfig.name }',
                name: '${ $.name }_provider',
                component: 'Magento_Ui/js/grid/search/options-provider'
            },
            imports: {
                options: '${ $.providerConfig.provider }:options'
            },
            listens: {
                options: 'dropNavigation'
            },
            modules: {
                provider: '${ $.providerConfig.provider }'
            }
        },

        /**
         * Initializes options component.
         *
         * @returns {Options} Chainable.
         */
        initialize: function () {
            this._super()
                .initProvider();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Options} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('options navigated');

            return this;
        },

        /**
         * Initializes options provider.
         *
         * @returns {Options} Chainable.
         */
        initProvider: function () {
            layout([this.providerConfig]);

            return this;
        },

        /**
         * Sets associated model.
         *
         * @param {Object} target - Object that handles selections result.
         * @returns {Options} Chainable.
         */
        setTarget: function (target) {
            this.target = target;

            return this;
        },

        /**
         * Sets container where options list is rendered.
         *
         * @param {HTMLElement} container - Options container.
         * @returns {Options} Chainable.
         */
        setContainer: function (container) {
            this.$container = container;

            return this;
        },

        /**
         * Filters options by the specified query.
         *
         * @param {String} query - Query upon which options should be filtered.
         * @returns {Options} Chainable.
         */
        filter: function (query) {
            this.provider('filter', query);

            return this;
        },

        /**
         * Clears options list.
         *
         * @returns {Options} Chainable.
         */
        clear: function () {
            this.options([]);

            return this;
        },

        /**
         * Selects specified option.
         *
         * @param {Object} option - Option to be selected.
         * @returns {Options} Chainable.
         */
        select: function (option) {
            var target  = this.target,
                model   = target.model;

            this.close();

            model.set(target.select, option[this.valueKey]);

            return this;
        },

        /**
         * Navigates to the option based on the specified offset.
         *
         * @param {Number} offset - Offset to be applied to the current option index.
         * @param {Boolean} [isIndex=false] - Flag that specifies whether
         *      first parameter is a direct index or an offset.
         * @returns {Options} Chainable.
         */
        navigate: function (offset, isIndex) {
            var index   = this._calculateIndex(offset, isIndex),
                target  = this.target,
                model   = target.model,
                option;

            this.navigated(index);

            if (~index) {
                option = this.options()[index];

                this._scrollTo(index);

                model.set(target.navigate, option[this.valueKey]);
            }

            return this;
        },

        /**
         * Navigates to the previous option.
         *
         * @returns {Options} Chainable.
         */
        prev: function () {
            this.navigate(-1);

            return this;
        },

        /**
         * Navigates to the next option.
         *
         * @returns {Options} Chainable.
         */
        next: function () {
            this.navigate(1);

            return this;
        },

        /**
         * Drops navigated option index.
         *
         * @returns {Options} Chainable.
         */
        dropNavigation: function () {
            this.navigated(-1);

            return this;
        },

        /**
         * Defines whether options list can be displayed.
         *
         * @returns {Boolean}
         */
        isVisible: function () {
            return this.opened() && this.options().length;
        },

        /**
         * Applies actions matched with a specified key code.
         *
         * @param {Number} code - Key code.
         */
        applyKey: function (code) {
            var target = this.target,
                model = target.model;

            switch (code) {
                case 38:
                    this.prev();
                    break;

                case 40:
                    this.next();
                    break;

                default:
                    this.filter(model.get(target.value));
            }
        },

        /**
         * Scrolls options list to the option at a specified index.
         *
         * @param {Number} index - Index of an option.
         * @returns {Options} Chainable.
         */
        _scrollTo: function (index) {
            var elem = this.$container.children[index];

            if (!isInVieport(elem, this.$container)) {
                elem.scrollIntoView(false);
            }

            return this;
        },

        /**
         * Calculates index of an option based on the specfied offset
         * from the currently navigated index. Loops through indices if
         * resulting value is out of the array boundaries.
         *
         * @param {Number} offset - Offset that will be used.
         * @param {Boolean} [isIndex=false] - Flag that specifies whether
         *      first parameter is a direct index or an offset.
         * @returns {Number} Resulting option index.
         */
        _calculateIndex: function (offset, isIndex) {
            var total = this.options().length,
                index = isIndex ? offset : this.navigated() + offset;

            if (!total) {
                index = -1;
            } else if (total === index) {
                index = 0;
            } else if (index < 0) {
                index = total - 1;
            }

            return index;
        }
    });
});
